// Función para mostrar/ocultar el menú lateral
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.style.left === '0px') {
      sidebar.style.left = '-250px';
    } else {
      sidebar.style.left = '0px';
    }
  }

  // Manejo del formulario y botones
  document.addEventListener('DOMContentLoaded', function() {
    // Formulario de agregar profesor
    document.getElementById('form-profe').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch('../logica/agregar_profe.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Profesor agregado correctamente');
          location.reload();
        } else {
          alert('Error: ' + (data.message || 'No se pudo agregar el profesor'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar la solicitud');
      });
    });

    // Delegación de eventos para los botones dinámicos
    document.addEventListener('click', function(e) {
      // Botón de inactivar (eliminar)
      if (e.target.classList.contains('btn-inactivar')) {
        if (confirm('¿Está seguro de eliminar este profesor?')) {
          const idProfesor = e.target.getAttribute('data-id');
          inactivarProfesor(idProfesor);
        }
      }
      
      // Botón de reactivar
      if (e.target.classList.contains('btn-reactivar')) {
        if (confirm('¿Está seguro de reactivar este profesor?')) {
          const idProfesor = e.target.getAttribute('data-id');
          reactivarProfesor(idProfesor);
        }
      }
    });
  });

  // Función para inactivar profesor
  function inactivarProfesor(id) {
    fetch('../logica/inactivar_profe.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id_profe=${id}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Profesor inactivado correctamente');
        location.reload();
      } else {
        alert(data.message || 'Error al inactivar profesor');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error al procesar la solicitud');
    });
  }

  // Función para reactivar profesor
  function reactivarProfesor(id) {
    fetch('../logica/reactivar_profe.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id_profe=${id}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Profesor reactivado correctamente');
        location.reload();
      } else {
        alert(data.message || 'Error al reactivar profesor');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error al procesar la solicitud');
    });
  }


  // Cuando se selecciona un curso, cargar profesores disponibles
document.addEventListener('DOMContentLoaded', () => {
  const cursoSelect = document.getElementById('curso');
  const profesorSelect = document.getElementById('profesor');

  if (cursoSelect && profesorSelect) {
    cursoSelect.addEventListener('change', () => {
      const idCurso = cursoSelect.value;

      // Limpiar opciones anteriores
      profesorSelect.innerHTML = '<option value="">Seleccione un profesor</option>';

      if (!idCurso) return;

      fetch(`../logica/obtener_profesores.php?id_curso=${idCurso}`)
        .then(res => res.json())
        .then(data => {
          if (data.length === 0) {
            const opt = document.createElement('option');
            opt.textContent = "No hay profesores disponibles";
            profesorSelect.appendChild(opt);
            return;
          }

          data.forEach(profe => {
            const opt = document.createElement('option');
            opt.value = profe.id_profesor;
            opt.textContent = `${profe.nombre} ${profe.apellido}`;
            profesorSelect.appendChild(opt);
          });
        })
        .catch(err => {
          console.error("Error cargando profesores:", err);
          alert("Ocurrió un error al cargar profesores");
        });
    });
  }
});
