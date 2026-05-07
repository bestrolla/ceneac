document.addEventListener("DOMContentLoaded", () => {
  // Activar salón
  document.querySelectorAll(".btn-activar").forEach(button => {
    button.addEventListener("click", () => {
      const id = button.dataset.id;
      fetch("../logica/cambiar_estado_salon.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, estado: "activo" })
      }).then(() => location.reload());
    });
  });
});

 document.addEventListener("DOMContentLoaded", () => {
      const form = document.getElementById("form-salon");
      const idInput = document.getElementById("id_salon");
      const nombreInput = document.getElementById("nombre_salon");
      const matriculaInput = document.getElementById("matricula");
      const btnGuardar = document.getElementById("btn-guardar");

      document.querySelectorAll(".btn-editar").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.getAttribute("data-id");
          const nombre = btn.getAttribute("data-nombre");
          const matricula = btn.getAttribute("data-matricula");

          idInput.value = id;
          nombreInput.value = nombre;
          matriculaInput.value = matricula;
          btnGuardar.textContent = "Actualizar Salón";

          form.action = "../logica/agregar_salon.php"; // O usa editar_salon.php si lo separas
        });
      });
    });
