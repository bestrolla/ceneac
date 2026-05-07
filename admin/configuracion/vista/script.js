// Función para abrir/cerrar el sidebar
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar.style.width === "250px") {
    sidebar.style.width = "0";
  } else {
    sidebar.style.width = "250px";
  }
}

// Funcionalidad del modal y formulario
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById("studentModal");
  const openBtn = document.getElementById("openModalBtn");
  const closeBtns = document.querySelectorAll(".close-modal");
  const form = document.getElementById("studentForm");

  // Abrir modal
  openBtn.onclick = function() {
    modal.style.display = "block";
  }

  // Cerrar modal
  closeBtns.forEach(btn => {
    btn.onclick = function() {
      modal.style.display = "none";
      form.reset();
    }
  });

  // Cerrar modal al hacer clic fuera del contenido
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
      form.reset();
    }
  }

  // Manejar el envío del formulario con async/await
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch('../logica/AgregarEstudiante.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        const estudiante = result.data;

        // Crear fila nueva
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${estudiante.curso}</td>
          <td>${estudiante.cedula}</td>
          <td>${estudiante.nombre}</td>
          <td>${estudiante.apellido}</td>
          <td>${estudiante.telefono}</td>
          <td class="icons">
            <img src="img/trash3-fill.svg" alt="Eliminar">
            <img src="img/person-fill-check.svg" alt="validar">
            <img src="img/person-check-fill.svg" alt="historial">
            <img src="img/person-fill-add.svg" alt="Duplicar">
          </td>
        `;

        const tbody = document.querySelector('.student-table tbody');
        tbody.insertBefore(tr, tbody.firstChild); // Insertar al inicio

        // Cerrar modal y resetear formulario
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

  // Validar coincidencia antes de enviar
  document.getElementById('configForm').addEventListener('submit', function(e) {
    const pass1 = document.getElementById('nueva_contrasena').value;
    const pass2 = document.getElementById('confirmar_contrasena').value;

    if (pass1 !== pass2) {
      e.preventDefault();
      alert('Las contraseñas no coinciden.');
    }
  });


  document.getElementById('configForm').addEventListener('submit', function(e) {
  const pass1 = document.getElementById('nueva_contrasena').value;
  const pass2 = document.getElementById('confirmar_contrasena').value;
  const errorMsg = document.getElementById('errorMensaje');

  if (pass1 !== pass2) {
    e.preventDefault();
    errorMsg.style.display = 'block';
  } else {
    errorMsg.style.display = 'none';
  }
});
