document.querySelector('table.student-table tbody').addEventListener('click', async e => {
  const btn = e.target.closest('.btn-action');
  if (!btn) return;

  const cedula = btn.dataset.id;
  if (!cedula) {
    alert('Cédula inválida o no proporcionada');
    return;
  }

  if (btn.classList.contains('btn-delete')) {
    if (!confirm(`¿Seguro que deseas eliminar al estudiante con cédula ${cedula}?`)) return;

    try {
      const res = await fetch('../logica/eliminar_estudiante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cedula })
      });
      const data = await res.json();
      alert(data.success ? 'Estudiante eliminado correctamente' : 'Error: ' + data.message);
      if (data.success) location.reload();
    } catch (error) {
      console.error(error);
      alert('Error al conectar con el servidor');
    }

  } else if (btn.classList.contains('btn-approve')) {
    if (!confirm(`¿Seguro que deseas aprobar al estudiante con cédula ${cedula}?`)) return;

    try {
      const res = await fetch('../logica/aprobar_estudiante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cedula=${encodeURIComponent(cedula)}`
      });
      const data = await res.json();
      alert(data.success ? 'Estudiante aprobado correctamente' : 'Error: ' + data.message);
      if (data.success) location.reload();
    } catch (error) {
      console.error(error);
      alert('Error al conectar con el servidor');
    }

  } else if (btn.classList.contains('btn-history')) {
    // Mover a espera solo con cédula
    if (!confirm(`¿Seguro que deseas mover al estudiante con cédula ${cedula} a espera?`)) return;

    try {
      const res = await fetch('../logica/mover_espera_logica.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cedula })
      });
      const data = await res.json();
      alert(data.success ? data.message : 'Error al mover a espera: ' + data.message);
      if (data.success) location.reload();
    } catch (error) {
      console.error(error);
      alert('Error al mover a espera.');
    }
  } else if (btn.classList.contains('btn-duplicate')) {
    try {
      const res = await fetch(`../logica/buscar_estudiante.php?cedula=${encodeURIComponent(cedula)}`);
      const data = await res.json();
      if (data) {
        document.getElementById('ci').value = data.cedula || '';
        document.getElementById('firstName').value = data.nombre || '';
        document.getElementById('lastName').value = data.apellido || '';
        document.getElementById('phone').value = data.telefono || '';
        document.getElementById('course').value = '';
        document.getElementById('studentModal').style.display = 'block';
      } else {
        alert('No se encontró el estudiante para duplicar');
      }
    } catch (error) {
      console.error(error);
      alert('Error al obtener datos para duplicar');
    }
  }
});
