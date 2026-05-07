<?php
// Mensaje de error
echo "<script>
  alert('Error al iniciar sesión. Por favor, verifica tus datos.');
  window.location.href = 'index.php';
</script>";
exit;
?>


<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error</title>
</head>
<body>
  <h1>Error al iniciar sesión</h1>
  <p>Por favor, verifica tus datos e intenta nuevamente.</p>
  <a href="index.php">Volver al inicio de sesión</a>
</body>
</html>