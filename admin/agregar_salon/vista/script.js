// Función para abrir/cerrar el sidebar
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  sidebar.style.width = sidebar.style.width === "250px" ? "0" : "250px";
}

document.addEventListener("DOMContentLoaded", () => {
  // Modal de motivo
  const modalMotivo = document.getElementById("modal-motivo");
  const modal = document.getElementById("modal-editar");

  if (modalMotivo) modalMotivo.style.display = "none";
  if (modal) modal.style.display = "none";
  //if (!modalMotivo) return;
  
  const motivoInput = document.getElementById("motivo-texto");
  const motivoIdInput = document.getElementById("motivo-id-salon");
  const motivoForm = document.getElementById("form-motivo");
  const motivoClose = document.getElementById("modal-motivo-close");

  // Abrir modal al hacer clic en "Desactivar"
  document.body.addEventListener("click", (e) => {
  if (e.target.classList.contains("btn-inactivar")) {
    const id = e.target.dataset.id;
    motivoIdInput.value = id;
    motivoInput.value = "";
    modalMotivo.style.display = "flex";
  }
});

  // Enviar motivo al backend
  motivoForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = motivoIdInput.value;
    const motivo = motivoInput.value;

    fetch("../logica/cambiar_estado_salon.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, estado: "inactivo", motivo })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message || "Salón desactivado correctamente.");
      modalMotivo.style.display = "none";
      location.reload();
    })
    .catch(err => {
      console.error("Error al desactivar salón:", err);
      alert("Hubo un error al desactivar el salón.");
    });
  });

  // Cerrar modal de motivo
  motivoClose.addEventListener("click", () => {
  modalMotivo.style.display = "none";
});

  window.addEventListener("click", (event) => {
    if (event.target === modalMotivo) {
      modalMotivo.style.display = "none";
    }
  });

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

  // Eliminar salon
  document.body.addEventListener("click", (e) => {
  if (e.target.textContent.trim().toLowerCase() === "eliminar") {
    const id = e.target.dataset.id;

    if (confirm("¿Estás seguro de que deseas eliminar este salón? Esta acción no se puede deshacer.")) {
      fetch("../logica/eliminar_salon.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_salon: id })
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message || "Salón eliminado correctamente.");
        // Elimina la fila del DOM sin recargar
        const fila = e.target.closest("tr");
        if (fila) fila.remove();
      })
      .catch(err => {
        console.error("Error al eliminar salón:", err);
        alert("Hubo un error al eliminar el salón.");
      });
    }
  }
});


  // Modal edición (si lo usas en esta vista)
  //const modal = document.getElementById("modal-editar");
  const form = document.getElementById("form-editar-salon");
  const idInput = document.getElementById("id_salon");
  const nombreInput = document.getElementById("nombre_salon");
  const matriculaInput = document.getElementById("matricula");
  const btnGuardar = document.getElementById("btn-guardar");
  const modalClose = document.getElementById("modal-close");

  if (modal) {
    document.querySelectorAll(".btn-editar").forEach(btn => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        const nombre = btn.getAttribute("data-nombre");
        const matricula = btn.getAttribute("data-matricula");

        idInput.value = id;
        nombreInput.value = nombre;
        matriculaInput.value = matricula;
        btnGuardar.textContent = "Actualizar Salón";
        form.action = "../logica/editar_salon.php";
        modal.style.display = "block";
      });
    });

    modalClose.addEventListener("click", () => {
      modal.style.display = "none";
      form.reset();
      btnGuardar.textContent = "Agregar Salón";
      form.action = "../logica/agregar_salon.php";
    });

    window.addEventListener("click", (event) => {
      if (event.target == modal) {
        modal.style.display = "none";
        form.reset();
        btnGuardar.textContent = "Agregar Salón";
        form.action = "../logica/agregar_salon.php";
      }
    });
  }
});

