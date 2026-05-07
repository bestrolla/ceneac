document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('registroForm');
  const pages = document.querySelectorAll('.form-page');
  const progressSteps = document.querySelectorAll('.progress-step');
  const nextButtons = document.querySelectorAll('.next-btn');
  const prevButtons = document.querySelectorAll('.prev-btn');
  let currentPage = 0;

  // Mostrar la primera página al cargar
  showPage(currentPage);

  // Event listeners para botones Siguiente
  nextButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      if (validateCurrentPage()) {
        currentPage++;
        showPage(currentPage);
        updateProgressBar();
      }
    });
  });

  // Event listeners para botones Anterior
  prevButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      currentPage--;
      showPage(currentPage);
      updateProgressBar();
    });
  });

  // Validación al enviar el formulario (manejado por PHP tradicional)
  form.addEventListener('submit', function(e) {
    // Validar todas las páginas antes de enviar
    if (!validateAllPages()) {
      e.preventDefault();
    }
  });

  // Función para mostrar una página específica
  function showPage(pageIndex) {
    pages.forEach((page, index) => {
      page.classList.toggle('active', index === pageIndex);
    });
  }

  // Función para actualizar la barra de progreso
  function updateProgressBar() {
    progressSteps.forEach((step, index) => {
      step.classList.toggle('active', index <= currentPage);
    });
  }

  // Función para validar la página actual
  function validateCurrentPage() {
    let isValid = true;
    const currentPageElements = pages[currentPage].querySelectorAll('input[required]');
    
    currentPageElements.forEach(input => {
      const errorMessage = input.parentElement.querySelector('.error-message');
      
      // Validación básica de campos vacíos
      if (!input.value.trim()) {
        showError(input, 'Este campo es obligatorio');
        isValid = false;
        return;
      }
      
      // Validaciones específicas por campo
      if (input.id === 'cedula' && !validateCedula(input.value)) {
        showError(input, 'Cédula no válida (6-10 dígitos)');
        isValid = false;
        return;
      }
      
      if (input.id === 'correo' && !validateEmail(input.value)) {
        showError(input, 'Correo electrónico no válido');
        isValid = false;
        return;
      }
      
      if (input.id === 'telefono' && !validatePhone(input.value)) {
        showError(input, 'Teléfono no válido (10-15 dígitos)');
        isValid = false;
        return;
      }
      
      if (input.id === 'usuario' && !validateUsername(input.value)) {
        showError(input, 'Usuario no válido (mínimo 6 caracteres con letras y números)');
        isValid = false;
        return;
      }
      
      if ((input.id === 'contrasena' || input.id === 'confirmar-contrasena') && !validatePassword(input.value)) {
        showError(input, 'La contraseña no cumple los requisitos');
        isValid = false;
        return;
      }
      
      if (input.id === 'confirmar-contrasena' && input.value !== document.getElementById('contrasena').value) {
        showError(input, 'Las contraseñas no coinciden');
        isValid = false;
        return;
      }
      
      // Si pasa todas las validaciones, ocultar mensaje de error
      hideError(input);
    });
    
    return isValid;
  }

  // Función para validar todas las páginas antes de enviar
  function validateAllPages() {
    let allValid = true;
    
    // Guardar la página actual para restaurarla después
    const originalPage = currentPage;
    
    for (let i = 0; i < pages.length; i++) {
      currentPage = i;
      if (!validateCurrentPage()) {
        allValid = false;
        // Mostrar la primera página con error encontrado
        showPage(i);
        updateProgressBar();
        break;
      }
    }
    
    // Restaurar la página original si todas las validaciones pasaron
    if (allValid) {
      currentPage = originalPage;
    } else {
      // Hacer scroll hasta el primer error
      const firstError = document.querySelector('.error-message[style="display: block;"]');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
    
    return allValid;
  }

  // Funciones de validación específicas
  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  function validatePhone(phone) {
    const re = /^[0-9]{10,15}$/;
    return re.test(phone);
  }

  function validateCedula(cedula) {
    const re = /^[0-9]{6,10}$/;
    return re.test(cedula);
  }

  function validateUsername(username) {
    const re = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
    return re.test(username);
  }

  function validatePassword(password) {
    // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return re.test(password);
  }

  // Funciones para mostrar/ocultar errores
  function showError(input, message) {
    const errorMessage = input.parentElement.querySelector('.error-message');
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
    input.style.borderColor = '#f44336';
  }

  function hideError(input) {
    const errorMessage = input.parentElement.querySelector('.error-message');
    errorMessage.style.display = 'none';
    input.style.borderColor = '';
  }

  // Validación en tiempo real para campos
  document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', function() {
      if (this.value.trim()) {
        hideError(this);
      }
    });
  });
});



  // Mostrar/ocultar contraseñas
  document.querySelectorAll('.toggle-password').forEach((btn, index) => {
    const input = btn.previousElementSibling;
    const showIcon = btn.querySelector(`#showPasswordIcon${index + 1}`);
    const hideIcon = btn.querySelector(`#hidePasswordIcon${index + 1}`);

    btn.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      showIcon.style.display = isPassword ? 'none' : 'inline';
      hideIcon.style.display = isPassword ? 'inline' : 'none';
    });
  });

  // Validación de contraseña al enviar
  document.querySelector('form').addEventListener('submit', function (e) {
    const usuario = document.getElementById('usuario');
    const pass1 = document.getElementById('contrasena');
    const pass2 = document.getElementById('confirmar-contrasena');

    const errorUsuario = document.getElementById('errorUsuario');
    const errorContrasena = document.getElementById('errorContrasena');
    const errorConfirmar = document.getElementById('errorConfirmar');

    let valido = true;

    // Validar usuario
    const usuarioValido = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/.test(usuario.value);
    if (!usuarioValido) {
      errorUsuario.textContent = "Debe contener letras y números, mínimo 6 caracteres.";
      valido = false;
    } else {
      errorUsuario.textContent = "";
    }

    // Validar contraseña segura
    const contrasenaSegura = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/.test(pass1.value);
    if (!contrasenaSegura) {
      errorContrasena.textContent = "Debe tener mayúscula, minúscula, número y símbolo.";
      valido = false;
    } else {
      errorContrasena.textContent = "";
    }

    // Validar coincidencia
    if (pass1.value !== pass2.value) {
      errorConfirmar.textContent = "Las contraseñas no coinciden.";
      valido = false;
    } else {
      errorConfirmar.textContent = "";
    }

    if (!valido) {
      e.preventDefault();
    }
  });




