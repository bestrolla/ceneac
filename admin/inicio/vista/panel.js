document.addEventListener('DOMContentLoaded', () => {
  const selectCurso = document.getElementById('curso');
  const profesorSelect = document.getElementById('profesor');
  const diaSelect = document.getElementById('dia');
  const turnoSelect = document.getElementById('turno');
  const horaInput = document.getElementById('hora');
  const fechaInput = document.getElementById('fecha');
  const tablaClases = document.getElementById('tabla-clases');
  const guardarBtn = document.getElementById('guardar-clase');

  let diasCurso = [];

  // ==========================
  // Cargar cursos
  // ==========================
  async function cargarCursos() {
    try {
      const res = await fetch('obtener_cursos.php');
      const data = await res.json();
      if (!data.success) throw new Error(data.message);

      selectCurso.innerHTML = '<option value="">-- Selecciona un curso --</option>';
      data.cursos.forEach(curso => {
        const option = document.createElement('option');
        option.value = curso.id_curso;
        option.textContent = `${curso.nombre} - Nivel ${curso.nivel}`;
        option.dataset.idcalendario = curso.id_calendario;
        option.dataset.fechainicio = curso.fecha_inicio;
        option.dataset.fechafin = curso.fecha_fin;
        option.dataset.dias = curso.dias;
        selectCurso.appendChild(option);
      });
    } catch (error) {
      alert('Error al cargar cursos: ' + error.message);
    }
  }

  // ==========================
  // Cargar profesores según curso
  // ==========================
  async function cargarProfesores(cursoId) {
    profesorSelect.innerHTML = '<option value="">Seleccione un profesor</option>';
    if (!cursoId) return;

    try {
      const response = await fetch(`../logica/get_profesores.php?curso=${cursoId}`);
      const data = await response.json();

      if (data.length === 0) {
        profesorSelect.innerHTML = '<option value="">No hay profesores disponibles</option>';
      } else {
        data.forEach(profe => {
          const option = document.createElement("option");
          option.value = profe.id_profesor;
          option.textContent = `${profe.nombre} ${profe.apellido}`;
          profesorSelect.appendChild(option);
        });
      }
    } catch (error) {
      console.error("Error al cargar profesores:", error);
    }
  }

  // ==========================
  // Cuando se selecciona un curso
  // ==========================
  selectCurso.addEventListener('change', async () => {
    tablaClases.innerHTML = '';
    diaSelect.innerHTML = '<option value="">-- Selecciona un día --</option>';
    horaInput.value = '';
    fechaInput.value = '';

    if (!selectCurso.value) {
      diasCurso = [];
      return;
    }

    // Llenar select de días
    const diasTexto = selectCurso.selectedOptions[0].dataset.dias || '';
    diasCurso = diasTexto.split(',').map(d => d.trim().toLowerCase());

    diasCurso.forEach(dia => {
      const option = document.createElement('option');
      option.value = dia;
      option.textContent = dia.charAt(0).toUpperCase() + dia.slice(1);
      diaSelect.appendChild(option);
    });

    // Cargar clases y profesores
    await cargarClases(selectCurso.value);
    await cargarProfesores(selectCurso.value);
  });

  // ==========================
  // Cargar clases
  // ==========================
  async function cargarClases(idCurso) {
    try {
      const res = await fetch(`obtener_clases.php?idCurso=${idCurso}`);
      const data = await res.json();
      if (!data.success) throw new Error(data.message);

      if (data.clases.length === 0) {
        tablaClases.innerHTML = '<p>No hay clases programadas para este curso.</p>';
        return;
      }

      const tabla = document.createElement('table');
      tabla.innerHTML = `
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Día</th>
            <th>Horario</th>
            <th>Estado</th>
          </tr>
        </thead>
      `;
      const tbody = document.createElement('tbody');
      data.clases.forEach(clase => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${clase.fecha_clase}</td>
          <td>${clase.dia.charAt(0).toUpperCase() + clase.dia.slice(1)}</td>
          <td>${clase.horario}</td>
          <td>${clase.estado}</td>
        `;
        tbody.appendChild(tr);
      });
      tabla.appendChild(tbody);
      tablaClases.innerHTML = '';
      tablaClases.appendChild(tabla);
    } catch (error) {
      tablaClases.innerHTML = '<p>Error al cargar las clases.</p>';
      console.error(error);
    }
  }

  // ==========================
  // Calcular horario por día y turno
  // ==========================
  function calcularHorario(dia, turno) {
    if (!dia || !turno) return '';

    const horarios = {
      lunes: { manana: '09:00-13:00', tarde: '13:00-17:00' },
      martes: { manana: '09:00-13:00', tarde: '13:00-17:00' },
      miércoles: { manana: '09:00-13:00', tarde: '13:00-17:00' },
      jueves: { manana: '09:00-13:00', tarde: '13:00-17:00' },
      viernes: { manana: '09:00-13:00', tarde: '13:00-17:00' },
      sábado: { manana: '08:00-13:00', tarde: '13:00-18:00' }
    };

    return horarios[dia]?.[turno] || '';
  }

  diaSelect.addEventListener('change', () => {
    horaInput.value = calcularHorario(diaSelect.value, turnoSelect.value);
  });

  turnoSelect.addEventListener('change', () => {
    horaInput.value = calcularHorario(diaSelect.value, turnoSelect.value);
  });

  // ==========================
  // Guardar nueva clase
  // ==========================
  guardarBtn.addEventListener('click', async () => {
    if (!selectCurso.value) return alert('Selecciona un curso');
    if (!diaSelect.value) return alert('Selecciona un día');
    if (!turnoSelect.value) return alert('Selecciona un turno');
    if (!horaInput.value) return alert('El horario no está definido');
    if (!fechaInput.value) return alert('Selecciona una fecha');

    const payload = {
      idCurso: selectCurso.value,
      dia: diaSelect.value,
      turno: turnoSelect.value,
      horario: horaInput.value,
      fecha: fechaInput.value
    };

    try {
      const res = await fetch('guardar_clase.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        alert('Clase guardada correctamente');
        await cargarClases(selectCurso.value);
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      alert('Error al guardar la clase.');
      console.error(error);
    }
  });

  // Inicializar
  cargarCursos();
});
