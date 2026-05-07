document.addEventListener('DOMContentLoaded', function() {
  // Toggle para mostrar/ocultar contraseña - IDs actualizados
  const togglePassword = document.querySelector('.toggle-password');
  const passwordInput = document.getElementById('passwordInput');
  const showIcon = document.getElementById('showPasswordIcon');
  const hideIcon = document.getElementById('hidePasswordIcon');

  if (togglePassword && passwordInput && showIcon && hideIcon) {
    togglePassword.addEventListener('click', function() {
      // Cambiar tipo de input
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      
      // Alternar iconos
      showIcon.style.display = isPassword ? 'none' : 'block';
      hideIcon.style.display = isPassword ? 'block' : 'none';
      
      // Actualizar atributo ARIA
      togglePassword.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  }

  // El resto de tu código para el modal y otros eventos permanece igual
  const forgotPasswordLink = document.getElementById('forgot-password-link');
  const modal = document.getElementById('forgot-password-modal');
  const closeModal = document.querySelector('.close-modal');
  const recoveryForm = document.getElementById('recoveryForm');

  if (forgotPasswordLink && modal) {
    forgotPasswordLink.addEventListener('click', function(e) {
      e.preventDefault();
      modal.style.display = 'flex';
    });
  }

  if (closeModal) {
    closeModal.addEventListener('click', function() {
      modal.style.display = 'none';
    });
  }

  window.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  if (recoveryForm) {
    recoveryForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = this.email.value;
      console.log('Enviando enlace de recuperación a:', email);
      alert(`Se ha enviado un enlace de recuperación a ${email}`);
      modal.style.display = 'none';
      this.reset();
    });
  }

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      // Validaciones del lado cliente
      const usuario = this.usuario.value.trim();
      const contrasena = this.contrasena.value;
      
      // Validar campos requeridos
      if (!usuario || !contrasena) {
        e.preventDefault();
        showError('Usuario y contraseña son requeridos');
        return;
      }
      
      // Validar longitud mínima de contraseña
      if (contrasena.length < 8) {
        e.preventDefault();
        showError('La contraseña debe tener al menos 8 caracteres');
        return;
      }
      
      // Validar formato de usuario (solo letras, números y guiones bajos)
      const usernamePattern = /^[a-zA-Z0-9_]+$/;
      if (!usernamePattern.test(usuario)) {
        e.preventDefault();
        showError('El usuario solo puede contener letras, números y guiones bajos');
        return;
      }
      
      // Mostrar indicador de carga
      const submitBtn = this.querySelector('button[type="submit"]');
      const loader = document.getElementById('formLoader');
      if (submitBtn && loader) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Iniciando sesión...';
        loader.style.display = 'block';
      }
    });
  }

  // Función para mostrar errores
  function showError(message) {
    // Remover alertas existentes
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Crear nueva alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.textContent = message;
    
    // Insertar antes del formulario
    const loginBox = document.querySelector('.login-box');
    const form = document.getElementById('loginForm');
    if (loginBox && form) {
      loginBox.insertBefore(alertDiv, form);
    }
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
      alertDiv.remove();
    }, 5000);
  }
});