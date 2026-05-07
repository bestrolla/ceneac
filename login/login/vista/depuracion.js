document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const usuarioInput = document.getElementById("usuarioInput");
  const passwordInput = document.getElementById("passwordInput");

  loginForm.addEventListener("submit", (e) => {
    let errores = [];

    // Validación de usuario
    const usuario = usuarioInput.value.trim();
    if (usuario.length < 4) {
      errores.push("El usuario debe tener al menos 4 caracteres.");
    }
    if (!/^[a-zA-Z0-9*$.]+$/.test(usuario)) {
      errores.push("El usuario solo puede contener letras, números y * $ .");
    }

    // Validación de contraseña
    const password = passwordInput.value.trim();
    if (password.length < 8) {
      errores.push("La contraseña debe tener mínimo 8 caracteres.");
    }
    if (!/[A-Z]/.test(password)) {
      errores.push("La contraseña debe tener al menos una letra mayúscula.");
    }
    if (!/[a-z]/.test(password)) {
      errores.push("La contraseña debe tener al menos una letra minúscula.");
    }
    if (!/[0-9]/.test(password)) {
      errores.push("La contraseña debe tener al menos un número.");
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
      errores.push("La contraseña debe tener al menos un carácter especial.");
    }

    // Si hay errores, cancelar envío y mostrarlos
    if (errores.length > 0) {
      e.preventDefault();

      // Buscar si ya existe un div de errores, si no lo creamos
      let errorBox = document.querySelector(".alert-danger");
      if (!errorBox) {
        errorBox = document.createElement("div");
        errorBox.classList.add("alert", "alert-danger");
        loginForm.prepend(errorBox);
      }
      errorBox.innerHTML = errores.join("<br>");
    }
  });
});
