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
  const cursoSelect = document.getElementById('curso');
  const profesorSelect = document.getElementById('profesor');

  async function cargarProfesores(idCurso = null) {
    try {
      const url = idCurso 
        ? `../logica/get_profesores.php?id_curso=${idCurso}` 
        : `../logica/get_profesores.php`;

      console.log('Cargando profesores desde:', url);

      const response = await fetch(url);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('Respuesta del servidor:', data);

      // Verificar si hay error en la respuesta
      if (data.error) {
        console.error('Error del servidor:', data.error);
        return;
      }

      profesorSelect.innerHTML = '<option value="">Seleccione un profesor</option>';

      if (Array.isArray(data) && data.length > 0) {
        data.forEach(prof => {
          const option = document.createElement('option');
          option.value = prof.id_profesor;
          
          // Mostrar nombre, apellido y especialidad (siempre)
          let displayText = `${prof.nombre} ${prof.apellido}`;
          if (prof.especialidad && prof.especialidad.trim() !== '') {
            displayText += ` - ${prof.especialidad}`;
          } else {
            displayText += ` - Sin especialidad`;
          }
          
          option.textContent = displayText;
          profesorSelect.appendChild(option);
        });
        console.log(`Se cargaron ${data.length} profesores`);
        
        // Mostrar mensaje informativo si se seleccionó un curso
        if (idCurso) {
          const cursoSeleccionado = cursoSelect.options[cursoSelect.selectedIndex];
          if (cursoSeleccionado && cursoSeleccionado.text !== 'Seleccione un curso') {
            console.log(`Mostrando profesores compatibles para: ${cursoSeleccionado.text}`);
          }
        }
      } else {
        console.log('No se encontraron profesores');
        if (idCurso) {
          profesorSelect.innerHTML = '<option value="">No hay profesores disponibles para este curso</option>';
        } else {
          profesorSelect.innerHTML = '<option value="">No hay profesores disponibles</option>';
        }
      }
    } catch (error) {
      console.error('Error al cargar profesores:', error);
      profesorSelect.innerHTML = '<option value="">Error al cargar profesores</option>';
    }
  }

  // Al cargar la página → mostrar todos
  cargarProfesores();

  // Cuando elija curso → filtrar profesor
  cursoSelect.addEventListener('change', (e) => {
    const idCurso = e.target.value;
    if (idCurso) {
      console.log('Curso seleccionado, filtrando profesores...');
    }
    cargarProfesores(idCurso || null);
  });
});

