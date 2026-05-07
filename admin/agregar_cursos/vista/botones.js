document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-curso');
  const tablaActivos = document.getElementById('tabla-cursos');
  const tablaInactivos = document.getElementById('tabla-cursos-inactivos');

  // Agregar curso (submit formulario)
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    try {
      const res = await fetch('../logica/agregar_cursos.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        alert('Curso agregado correctamente');
        location.reload();
      } else {
        alert('Error al agregar curso: ' + (data.message || 'Error desconocido'));
      }
    } catch (error) {
      alert('Error en la conexión: ' + error.message);
    }
  });

  // Delegación para activar/desactivar
  document.body.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-inactivar') || e.target.classList.contains('btn-activar')) {
      const idCurso = e.target.getAttribute('data-id');
      const nuevoEstado = e.target.classList.contains('btn-inactivar') ? 'inactivo' : 'activo';
      const confirmMsg = nuevoEstado === 'inactivo' ? '¿Seguro que deseas desactivar este curso?' : '¿Seguro que deseas activar este curso?';

      // if (!confirm(confirmMsg)) return;

      try {
        const res = await fetch('../logica/cambiar_estado.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_curso: idCurso, estado: nuevoEstado })
        });
        const data = await res.json();

        if (data.success) {
          alert(`Curso ${nuevoEstado === 'activo' ? 'activado' : 'desactivado'} correctamente`);
          location.reload();
        } else {
          alert('Error: ' + (data.message || 'Error desconocido'));
        }
      } catch (error) {
        alert('Error en la conexión: ' + error.message);
      }
    }
  });
});



