document.querySelector('.student-table tbody').addEventListener('click', async function(e) {
  const btn = e.target.closest('.btn-action');
  if (!btn) return;

  const cedula = btn.getAttribute('data-cedula');
  const idCurso = btn.getAttribute('data-curso');
  const nombreCurso = btn.getAttribute('data-nombre-curso') || idCurso;

  if (!cedula) {
    alert('Falta la cédula para realizar la acción');
    return;
  }

  if (btn.classList.contains('btn-move-lobby')) {
    if (!confirm(`¿Seguro que deseas aprobar al estudiante con cédula ${cedula}?`)) return;

    try {
      const res = await fetch('../logica/aprobar_estudiante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cedula, id_curso: idCurso })
      });

      const data = await res.json();

      if (data.success) {
        alert('Estudiante aprobado exitosamente');
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Error desconocido'));
      }
    } catch (error) {
      console.error('Error al aprobar estudiante:', error);
      alert('Error al procesar la solicitud');
    }
  }

  if (btn.classList.contains('btn-delete')) {
    if (!idCurso) {
      alert('Falta el ID del curso para eliminar');
      return;
    }
    if (!confirm(`¿Seguro que deseas eliminar al estudiante con cédula ${cedula} del curso ${nombreCurso}?`)) return;

    try {
      const res = await fetch('../logica/eliminar_estudiante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cedula, id_curso: idCurso })
      });

      const data = await res.json();

      if (data.success) {
        alert('Estudiante eliminado correctamente');
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Error desconocido'));
      }
    } catch (error) {
      console.error('Error al eliminar estudiante:', error);
      alert('Error al conectar con el servidor');
    }
  }
});
