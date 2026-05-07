document.addEventListener('DOMContentLoaded', () => {
  const configForm = document.getElementById('configForm');

  configForm.addEventListener('submit', (e) => {
    const nuevoUsuario = configForm.nuevo_usuario.value.trim();
    const nuevaContrasena = configForm.nueva_contrasena.value;
    const confirmarContrasena = configForm.confirmar_contrasena.value;

    let errores = [];

    if (!nuevoUsuario) {
      errores.push("El nombre de usuario no puede estar vacío.");
    }

    if (nuevaContrasena.length < 8) {
      errores.push("La contraseña debe tener al menos 8 caracteres.");
    }

    if (nuevaContrasena !== confirmarContrasena) {
      errores.push("Las contraseñas no coinciden.");
    }

    if (errores.length > 0) {
      e.preventDefault();
      alert("Errores:\n" + errores.join("\n"));
    }
  });

  // Toggle mostrar/ocultar contraseña
  const toggleButtons = document.querySelectorAll('.toggle-password');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
      } else {
        input.type = 'password';
      }
    });
  });
});
