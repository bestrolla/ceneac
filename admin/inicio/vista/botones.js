document.addEventListener('DOMContentLoaded', () => {
  const horariosPorDia = {
    lunes: { manana: "09:00-13:00", tarde: "13:00-17:00" },
    martes: { manana: "09:00-13:00", tarde: "13:00-17:00" },
    miercoles: { manana: "09:00-13:00", tarde: "13:00-17:00" },
    jueves: { manana: "09:00-13:00", tarde: "13:00-17:00" },
    viernes: { manana: "09:00-13:00", tarde: "13:00-17:00" },
    sabado: { manana: "08:00-13:00", tarde: "13:00-18:00" }
  };

  const cursoSelect   = document.getElementById('curso');
  const diaSelect     = document.getElementById('dia');
  const turnoSelect   = document.getElementById('turno');
  const horaInput     = document.getElementById('hora');
  const fechaInput    = document.getElementById('fecha');
  const profesorSelect= document.getElementById('profesor');
  const guardarBtn    = document.getElementById('guardar-clase');

  // Al cambiar curso
  cursoSelect.addEventListener('change', () => {
    const option = cursoSelect.options[cursoSelect.selectedIndex];
    const diasStr = option.getAttribute('data-dias') || '';
    const fechaInicio = option.getAttribute('data-fechainicio') || '';
    const fechaFin = option.getAttribute('data-fechafin') || '';
    const idCurso = cursoSelect.value;

    // --- Manejo de días
    diaSelect.innerHTML = '<option value="">-- Selecciona un día --</option>';
    const dias = diasStr.split(',').map(d => d.trim().toLowerCase()).filter(d => d);
    dias.forEach(dia => {
      const opt = document.createElement('option');
      opt.value = dia;
      opt.textContent = dia.charAt(0).toUpperCase() + dia.slice(1);
      diaSelect.appendChild(opt);
    });

    // --- Limitar fechas
    fechaInput.min = fechaInicio;
    fechaInput.max = fechaFin;
    fechaInput.value = '';

    // --- Reset turno y hora
    turnoSelect.value = '';
    horaInput.value = '';

    // --- Cargar profesores via fetch
    profesorSelect.innerHTML = '<option value="">Seleccione un profesor</option>';
    if (idCurso) {
      fetch(`../logica/get_profesores.php?id_curso=${idCurso}`)
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
        .catch(err => console.error("Error cargando profesores:", err));
    }
  });

  // --- Actualizar horario
  function actualizarHorario() {
    const dia = diaSelect.value;
    const turno = turnoSelect.value;
    if (dia && turno && horariosPorDia[dia] && horariosPorDia[dia][turno]) {
      horaInput.value = horariosPorDia[dia][turno];
    } else {
      horaInput.value = '';
    }
  }
  diaSelect.addEventListener('change', actualizarHorario);
  turnoSelect.addEventListener('change', actualizarHorario);

  // --- Botón guardar
  if (guardarBtn) {
    guardarBtn.addEventListener('click', () => {
      const curso = cursoSelect.value;
      const dia = diaSelect.value;
      const turno = turnoSelect.value;
      const hora = horaInput.value;
      const fecha = fechaInput.value;
      const profesor = profesorSelect.value;

      if (!curso || !dia || !turno || !hora || !fecha || !profesor) {
        alert('Por favor completa todos los campos antes de guardar.');
        return;
      }

      alert(`Clase programada:
Curso: ${curso}
Profesor: ${profesor}
Día: ${dia}
Turno: ${turno}
Horario: ${hora}
Fecha: ${fecha}`);
    });
  }
});
