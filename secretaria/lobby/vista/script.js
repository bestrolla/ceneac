// Función para abrir/cerrar el sidebar
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar.style.width === "250px") {
    sidebar.style.width = "0";
  } else {
    sidebar.style.width = "250px";
  }
}




document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById("studentModal");
  const openBtn = document.getElementById("openModalBtn");
  const closeBtns = document.querySelectorAll(".close-modal");
  const form = document.getElementById("studentForm");

  // Abrir modal
  openBtn.onclick = () => {
    modal.style.display = "block";
  };

  // Cerrar modal
  closeBtns.forEach(btn => {
    btn.onclick = () => {
      modal.style.display = "none";
      form.reset();
    };
  });

  // Cerrar modal al hacer clic fuera del contenido
  window.onclick = e => {
    if (e.target === modal) {
      modal.style.display = "none";
      form.reset();
    }
  };

  // Manejar envío formulario agregar estudiante
  form.addEventListener('submit', async e => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const res = await fetch('../logica/AgregarEstudiante.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();

      if (result.success) {
        const estudiante = result.data;

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${estudiante.curso}</td>
          <td>${estudiante.cedula}</td>
          <td>${estudiante.nombre}</td>
          <td>${estudiante.apellido}</td>
          <td>${estudiante.telefono ?? ''}</td>
          <td class="icons">
            <button class="btn-action btn-delete" data-id="${estudiante.cedula}" title="Eliminar">
              <img src="img/trash3-fill.svg" alt="Eliminar" />
            </button>
            <button class="btn-action btn-approve" data-id="${estudiante.cedula}" title="Aprobar">
              <img src="img/person-fill-check.svg" alt="Aprobar" />
            </button>
            <button class="btn-action btn-history" data-id="${estudiante.cedula}" title="Mover a Espera">
              <img src="img/person-lines-fill.svg" alt="Mover a Espera" />
            </button>
            <button class="btn-action btn-duplicate" data-id="${estudiante.cedula}" title="Duplicar">
              <img src="img/person-fill-add.svg" alt="Duplicar" />
            </button>
          </td>
        `;

        const tbody = document.querySelector('.student-table tbody');
        tbody.insertBefore(tr, tbody.firstChild);

        modal.style.display = 'none';
        form.reset();
      } else {
        alert('Error: ' + result.message);
      }
    } catch (err) {
      alert('Error de conexión con el servidor.');
      console.error(err);
    }
  });
});
