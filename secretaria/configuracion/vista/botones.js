document.addEventListener('DOMContentLoaded', function() {
  // Delegación de eventos para los botones de acción en la tabla
  document.querySelector('table.student-table tbody').addEventListener('click', async function(e) {
    const btn = e.target.closest('.btn-action');
    if (!btn) return;

    const cedula = btn.dataset.id;
    if (!cedula) return;

    if (btn.classList.contains('btn-delete')) {
      await eliminarEstudiante(cedula, btn);
    } else if (btn.classList.contains('btn-approve')) {
      await aprobarEstudiante(cedula);
    } else if (btn.classList.contains('btn-history')) {
      await moverAEspera(cedula, btn);
    } else if (btn.classList.contains('btn-duplicate')) {
      await duplicarEstudiante(cedula);
    }
  });

  async function eliminarEstudiante(cedula, btn) {
    if (!confirm(`¿Estás seguro de eliminar al estudiante con cédula ${cedula}?`)) return;

    try {
      const response = await fetch('../logica/eliminar_estudiante.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `cedula=${encodeURIComponent(cedula)}`
      });
      const data = await response.json();

      if (data.success) {
        alert('Estudiante eliminado correctamente');
        // Quitar fila sin recargar
        const fila = btn.closest('tr');
        if (fila) fila.remove();
      } else {
        alert('Error: ' + (data.message || 'No se pudo eliminar el estudiante'));
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error al conectar con el servidor');
    }
  }

  async function aprobarEstudiante(cedula) {
    try {
      const response = await fetch('../logica/aprobar_estudiante.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `cedula=${encodeURIComponent(cedula)}`
      });
      const data = await response.json();

      if (data.success) {
        alert('Estudiante aprobado correctamente');
        location.reload(); // Recargar para actualizar estado
      } else {
        alert('Error: ' + (data.message || 'No se pudo aprobar el estudiante'));
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error al conectar con el servidor');
    }
  }

 async function moverAEspera(cedula) {
  if (!confirm(`¿Quieres mover la cédula ${cedula} a lista de espera?`)) return;

  try {
    const response = await fetch('../logica/cambiar_estatus_espera.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ cedula }) // envías la cédula en JSON
    });

    const data = await response.json();

    if (data.success) {
      alert(data.message);
      // Aquí puedes actualizar la tabla o recargar página
    } else {
      alert('Error: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al conectar con el servidor');
  }
}


  async function duplicarEstudiante(cedula) {
    try {
      const response = await fetch(`../logica/buscar_estudiante.php?cedula=${encodeURIComponent(cedula)}`);
      const data = await response.json();

      if (data) {
        // Completar formulario modal con datos recibidos
        document.getElementById('ci').value = data.cedula || '';
        document.getElementById('firstName').value = data.nombre || '';
        document.getElementById('lastName').value = data.apellido || '';
        document.getElementById('phone').value = data.telefono || '';
        document.getElementById('course').value = '';

        // Mostrar modal
        document.getElementById('studentModal').style.display = 'block';
      } else {
        alert('No se encontró el estudiante para duplicar');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error al obtener datos del estudiante');
    }
  }
});


// mover a todos a espera

document.querySelector('.cerrar-sesion').addEventListener('click', function(event) {
  event.preventDefault();

  if (confirm('¿Deseas cerrar sesión?\nLos estudiantes activos serán pasados a espera.')) {
    fetch('../logica/mover_todos_a_espera.php')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = '../../../verificacion/cerrar_sesion.php';
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error(error);
        alert('Error al mover estudiantes.');
      });
  }
});
