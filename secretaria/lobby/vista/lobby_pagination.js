document.addEventListener('DOMContentLoaded', () => {
  const tabla = document.getElementById('tabla-estudiantes');
  const buscador = document.getElementById('buscar-estudiante');
  const paginacion = document.getElementById('paginacion-estudiantes');

  let paginaActual = 1;
  let ultimaBusqueda = '';

  // === TOAST SYSTEM ===
  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    toastContainer.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 1000;';
    document.body.appendChild(toastContainer);
  }
  
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.style.cssText = `
      background: ${type === 'success' ? '#28a745' : '#dc3545'};
      color: white;
      padding: 10px 15px;
      margin-top: 5px;
      border-radius: 5px;
      animation: fadeInOut 4s ease-in-out forwards;
    `;
    toast.innerHTML = message;
    toastContainer.appendChild(toast);
    
    setTimeout(() => toast.remove(), 4000);
  }

  // === ESCAPE HTML ===
  const escapeHtml = text => {
    if (!text && text !== 0) return '';
    return String(text)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  // === Render tabla ===
  function renderTabla(estudiantes) {
    if (!estudiantes || estudiantes.length === 0) {
      tabla.innerHTML = `<tr><td colspan="6">No se encontraron estudiantes</td></tr>`;
      return;
    }
    tabla.innerHTML = estudiantes.map(e => `
      <tr data-id="${e.cedula}">
        <td>${escapeHtml(e.nombre_curso)}</td>
        <td>${escapeHtml(e.cedula)}</td>
        <td>${escapeHtml(e.nombre)}</td>
        <td>${escapeHtml(e.apellido)}</td>
        <td>${escapeHtml(e.telefono)}</td>
        <td class="icons">
          <button class="btn-action btn-delete" data-id="${e.cedula}" title="Eliminar">
            <img src="img/trash3-fill.svg" alt="Eliminar" />
          </button>
          <button class="btn-action btn-approve" data-id="${e.cedula}" title="Aprobar">
            <img src="img/person-fill-check.svg" alt="Aprobar" />
          </button>
          <button class="btn-action btn-history" data-id="${e.cedula}" title="Mover a Espera">
            <img src="img/person-lines-fill.svg" alt="Mover a Espera" />
          </button>
          <button class="btn-action btn-duplicate" data-id="${e.cedula}" title="Duplicar">
            <img src="img/person-fill-add.svg" alt="Duplicar" />
          </button>
        </td>
      </tr>
    `).join('');
  }

  // === Render paginación ===
  function renderPaginacion(current, total) {
    if (total <= 1) {
      paginacion.innerHTML = '';
      return;
    }
    
    paginacion.innerHTML = `
      <button id="pagina-anterior" ${current <= 1 ? 'disabled' : ''}>Anterior</button>
      <span>Página ${current} de ${total}</span>
      <button id="pagina-siguiente" ${current >= total ? 'disabled' : ''}>Siguiente</button>
    `;
  }

  // === Cargar estudiantes ===
  function cargarEstudiantes(query = '', page = 1) {
    console.log('Cargando estudiantes:', { query, page }); // Debug log
    
    // Show loading message
    tabla.innerHTML = `<tr><td colspan="6">Cargando estudiantes...</td></tr>`;
    
    const url = `../logica/buscar_estudiantes.php?q=${encodeURIComponent(query)}&page=${page}`;
    console.log('Fetching URL:', url); // Debug log
    
    fetch(url)
      .then(res => {
        console.log('Response status:', res.status, res.statusText); // Debug log
        console.log('Response headers:', res.headers.get('content-type')); // Debug log
        
        if (!res.ok) {
          throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }
        
        return res.text(); // Get as text first to check for non-JSON responses
      })
      .then(text => {
        console.log('Raw response text:', text.substring(0, 200) + '...'); // Debug log (first 200 chars)
        
        let data;
        try {
          data = JSON.parse(text);
        } catch (parseError) {
          throw new Error(`Invalid JSON response: ${parseError.message}. Response: ${text.substring(0, 100)}...`);
        }
        
        console.log('Parsed response data:', data); // Debug log
        
        if (!data.success) {
          const errorMsg = data.message || 'Error desconocido';
          tabla.innerHTML = `<tr><td colspan="6">❌ Error: ${errorMsg}</td></tr>`;
          console.error('Server error:', data);
          showToast(`Error del servidor: ${errorMsg}`, 'error');
          return;
        }
        
        if (!data.data || !Array.isArray(data.data)) {
          tabla.innerHTML = `<tr><td colspan="6">❌ Respuesta del servidor inválida</td></tr>`;
          console.error('Invalid data structure:', data);
          showToast('Respuesta del servidor inválida', 'error');
          return;
        }
        
        renderTabla(data.data);
        renderPaginacion(data.currentPage, data.pages);
        paginaActual = data.currentPage;
        ultimaBusqueda = query;
        
        // Success message for non-empty results
        if (data.data.length > 0) {
          console.log(`✅ Loaded ${data.data.length} students (page ${data.currentPage}/${data.pages}, total: ${data.total})`);
        }
      })
      .catch(error => {
        console.error('Fetch error details:', error); // Debug log
        const errorMsg = `Error de conexión: ${error.message}`;
        tabla.innerHTML = `<tr><td colspan="6">❌ ${errorMsg}</td></tr>`;
        showToast(errorMsg, 'error');
      });
  }

  // === Paginación ===
  paginacion.addEventListener('click', e => {
    if (e.target.id === 'pagina-anterior' && paginaActual > 1) {
      cargarEstudiantes(ultimaBusqueda, paginaActual - 1);
    }
    if (e.target.id === 'pagina-siguiente') {
      cargarEstudiantes(ultimaBusqueda, paginaActual + 1);
    }
  });

  // === Búsqueda en tiempo real ===
  let timer;
  buscador.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      cargarEstudiantes(buscador.value.trim(), 1);
    }, 300);
  });

  // === Carga inicial ===
  cargarEstudiantes();
});