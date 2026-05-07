function eliminarEstudiante(cedula) {
  if (!confirm("¿Estás seguro de que deseas eliminar al estudiante con cédula " + cedula + "?")) {
    return;
  }

  fetch('../logica/eliminar_estudiante.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ cedula: cedula })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Estudiante eliminado correctamente.');
      location.reload(); // Recarga la página
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error al eliminar:', error);
    alert('Ocurrió un error al intentar eliminar el estudiante.');
  });
}


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