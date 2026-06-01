<?php
$root = realpath(__DIR__ . '/../');
$patterns = [
    '/src\s*=\s*"([^"]+)"/i',
    "/src\s*=\s*'([^']+)'/i",
    '/href\s*=\s*"([^"]+)"/i',
    "/href\s*=\s*'([^']+)'/i",
    '/url\(\s*(?:["\']?)([^"\')]+)(?:["\']?)\s*\)/i'
];
$files = [];
$exts = ['png','jpg','jpeg','gif','svg','ico','webp','bmp','icon'];
$scan = function($dir) use (&$scan, &$files) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isFile()) {
            $name = $file->getFilename();
            $lower = strtolower($name);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (in_array($ext, ['php','html','htm','js','css'])) {
                $files[] = $file->getPathname();
            }
        }
    }
};
$scan($root);
$refs = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    foreach ($patterns as $pat) {
        if (preg_match_all($pat, $content, $m)) {
            foreach ($m[1] as $p) {
                $p = trim($p);
                if ($p === '') continue;
                // ignore absolute remote URLs
                if (preg_match('#^https?://#i', $p)) continue;
                // normalize remove query string
                $p = strtok($p, '?');
                $refs[$p][] = $f;
            }
        }
    }
}

$missing = [];
foreach ($refs as $ref => $locations) {
    $check = $ref;
    // handle /proto prefix
    if (strpos($check, '/proto/') === 0) {
        $check = substr($check, strlen('/proto'));
    }
    // handle leading slash
    if (strpos($check, '/') === 0) $check = substr($check,1);

    $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $check);
    if (file_exists($candidate)) {
        continue;
    }
    // try with direct relative (if ref is like img/foo.svg)
    $candidate2 = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ref);
    if (file_exists($candidate2)) continue;
    // try relative to the referencing file directory
    $foundRelative = false;
    foreach ($locations as $loc) {
        $dir = dirname($loc);
        $candRel = realpath($dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ref));
        if ($candRel && file_exists($candRel)) { $foundRelative = true; break; }
        $candRel2 = realpath($dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $check));
        if ($candRel2 && file_exists($candRel2)) { $foundRelative = true; break; }
    }
    if ($foundRelative) continue;

    $missing[$ref] = $locations;
}

if (empty($missing)) {
    echo "All referenced local assets exist.\n";
    exit(0);
}

echo "Missing asset references (path => referenced in):\n";
foreach ($missing as $ref => $locs) {
    echo "- {$ref}\n";
    foreach ($locs as $l) {
        echo "    - {$l}\n";
    }
}
exit(2);
