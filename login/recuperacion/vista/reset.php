<?php
/**
 * Página de restablecimiento de contraseña
 */

require_once '../../../core/Session.php';
require_once '../../../core/Security.php';
require_once '../../../core/Database.php';

$session = getSession();
$session->start();

$token = $_GET['token'] ?? '';
$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';

$errorMessages = [
    'invalid_token' => 'El enlace de recuperación no es válido o ha expirado',
    'password_mismatch' => 'Las contraseñas no coinciden',
    'weak_password' => 'La contraseña no cumple con los requisitos de seguridad',
    'system_error' => 'Error del sistema. Por favor intente más tarde'
];

$successMessages = [
    'password_reset' => 'Contraseña restablecida exitosamente. Puede iniciar sesión'
];

// Verificar token
$tokenValid = false;
if (!empty($token)) {
    $db = getDB();
    $sql = "SELECT pr.*, u.nombre_usuario 
            FROM password_recovery pr 
            JOIN usuario u ON pr.user_id = u.id_usuario 
            WHERE pr.token = :token AND pr.expires_at > NOW()";
    
    $recovery = $db->fetchOne($sql, ['token' => $token]);
    $tokenValid = !empty($recovery);
}

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Ceneac</title>
    <link rel="stylesheet" href="../../login/vista/style.css">
    <link rel="shortcut icon" href="../../login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <section class="container">
        <div class="login-box">
            <h2>Restablecer Contraseña</h2>

            <?php if (!empty($error) && isset($errorMessages[$error])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessages[$error]) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($message) && isset($successMessages[$message])): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($successMessages[$message]) ?>
                </div>
            <?php endif; ?>

            <?php if ($tokenValid): ?>
                <form id="resetForm" action="../logica/process_reset.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="textbox">
                        <label for="passwordInput">Nueva Contraseña</label>
                        <input type="password" name="password" id="passwordInput" placeholder="Nueva contraseña" required minlength="8">
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <img src="../../login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon">
                            <img src="../../login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon" style="display: none;">
                        </button>
                    </div>
                    
                    <div class="textbox">
                        <label for="confirmPasswordInput">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" id="confirmPasswordInput" placeholder="Confirmar contraseña" required minlength="8">
                        <button type="button" class="toggle-password-confirm" aria-label="Mostrar contraseña">
                            <img src="../../login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showConfirmPasswordIcon">
                            <img src="../../login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hideConfirmPasswordIcon" style="display: none;">
                        </button>
                    </div>
                    
                    <div class="password-requirements">
                        <p>La contraseña debe contener:</p>
                        <ul>
                            <li>Al menos 8 caracteres</li>
                            <li>Una letra mayúscula</li>
                            <li>Una letra minúscula</li>
                            <li>Un número</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn">Restablecer Contraseña</button>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    El enlace de recuperación no es válido o ha expirado.
                </div>
                <a href="/login/login/vista/index.php" class="btn" style="display: inline-block; text-decoration: none; text-align: center;">Volver al Login</a>
            <?php endif; ?>
        </div>
        
        <div class="logo-container">
            <img src="../../login/vista/img/logoceneac.png" alt="Logo Ceneac Producciones">
        </div>
    </section>

    <footer class="footer">
        <div class="footer-links">
            <a href="/inicio">Inicio</a>
            <a href="/servicios">Servicios</a>
            <a href="/portafolio">Portafolio</a>
            <a href="/contacto">Contacto</a>
            <a href="/terminos">Términos y condiciones</a>
            <a href="/privacidad">Política de privacidad</a>
        </div>
        <div class="footer-copyright">
            © 2025 Ceneac Producciones. Todos los derechos reservados.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle para contraseña principal
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('passwordInput');
            const showIcon = document.getElementById('showPasswordIcon');
            const hideIcon = document.getElementById('hidePasswordIcon');

            if (togglePassword && passwordInput && showIcon && hideIcon) {
                togglePassword.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    showIcon.style.display = isPassword ? 'none' : 'block';
                    hideIcon.style.display = isPassword ? 'block' : 'none';
                });
            }

            // Toggle para confirmar contraseña
            const toggleConfirmPassword = document.querySelector('.toggle-password-confirm');
            const confirmPasswordInput = document.getElementById('confirmPasswordInput');
            const showConfirmIcon = document.getElementById('showConfirmPasswordIcon');
            const hideConfirmIcon = document.getElementById('hideConfirmPasswordIcon');

            if (toggleConfirmPassword && confirmPasswordInput && showConfirmIcon && hideConfirmIcon) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const isPassword = confirmPasswordInput.type === 'password';
                    confirmPasswordInput.type = isPassword ? 'text' : 'password';
                    showConfirmIcon.style.display = isPassword ? 'none' : 'block';
                    hideConfirmIcon.style.display = isPassword ? 'block' : 'none';
                });
            }

            // Validación del formulario
            const resetForm = document.getElementById('resetForm');
            if (resetForm) {
                resetForm.addEventListener('submit', function(e) {
                    const password = this.password.value;
                    const confirmPassword = this.confirm_password.value;
                    
                    // Verificar que las contraseñas coincidan
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Las contraseñas no coinciden');
                        return;
                    }
                    
                    // Validar fortaleza de contraseña
                    const hasUpper = /[A-Z]/.test(password);
                    const hasLower = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasMinLength = password.length >= 8;
                    
                    if (!hasUpper || !hasLower || !hasNumber || !hasMinLength) {
                        e.preventDefault();
                        alert('La contraseña no cumple con los requisitos de seguridad');
                        return;
                    }
                });
            }
        });
    </script>

    <style>
        .password-requirements {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin: 2px 0;
            color: #666;
        }
    </style>
</body>
</html>
