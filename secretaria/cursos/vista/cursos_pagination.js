document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('cursos-container');
  const buscador = document.getElementById('buscar-cursos');
  const paginacion = document.getElementById('paginacion-cursos');

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

  // === Render cursos ===
  function renderCursos(cursos) {
    if (!cursos || cursos.length === 0) {
      container.innerHTML = `<p>No se encontraron cursos disponibles.</p>`;
      return;
    }
    container.innerHTML = cursos.map(c => `
      <section class="curso">
        <h2>${escapeHtml(c.nombre_curso || '')}</h2>
        <p><strong>Fecha de inicio:</strong> ${escapeHtml(c.fecha_inicio || 'No definida')}</p>
        <p><strong>Fecha de fin:</strong> ${escapeHtml(c.fecha_fin || 'No definida')}</p>
        <p><strong>Horario:</strong> ${escapeHtml(c.horario || 'No definido')}</p>
        <p><strong>Días:</strong> ${escapeHtml(c.dias || 'No definidos')}</p>
        <p><strong>Días festivos:</strong> ${escapeHtml(c.dias_festivo || 'Ninguno')}</p>
        <p><strong>Total de estudiantes inscritos:</strong> ${escapeHtml(c.total_estudiantes || 0)}</p>
        <button class="btn-lista-estudiantes" data-curso-id="${escapeHtml(c.id_curso)}">
          Lista de estudiantes
        </button>
      </section>
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

  // === Cargar cursos ===
  function cargarCursos(query = '', page = 1) {
    console.log('Cargando cursos:', { query, page }); // Debug log
    
    container.innerHTML = '<p>Cargando cursos...</p>';
    
    const url = `../logica/buscar_cursos.php?q=${encodeURIComponent(query)}&page=${page}`;
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
          container.innerHTML = `<p>❌ Error al cargar cursos: ${errorMsg}</p>`;
          console.error('Server error:', data);
          showToast(`Error del servidor: ${errorMsg}`, 'error');
          return;
        }
        
        if (!data.data || !Array.isArray(data.data)) {
          container.innerHTML = `<p>❌ Respuesta del servidor inválida</p>`;
          console.error('Invalid data structure:', data);
          showToast('Respuesta del servidor inválida', 'error');
          return;
        }
        
        renderCursos(data.data);
        renderPaginacion(data.currentPage, data.pages);
        paginaActual = data.currentPage;
        ultimaBusqueda = query;
        
        // Success message for non-empty results
        if (data.data.length > 0) {
          console.log(`✅ Loaded ${data.data.length} cursos (page ${data.currentPage}/${data.pages}, total: ${data.total})`);
        }
      })
      .catch(error => {
        console.error('Fetch error details:', error); // Debug log
        const errorMsg = `Error de conexión: ${error.message}`;
        container.innerHTML = `<p>❌ ${errorMsg}</p>`;
        showToast(errorMsg, 'error');
      });
  }

  // === Paginación ===
  paginacion.addEventListener('click', e => {
    if (e.target.id === 'pagina-anterior' && paginaActual > 1) {
      cargarCursos(ultimaBusqueda, paginaActual - 1);
    }
    if (e.target.id === 'pagina-siguiente') {
      cargarCursos(ultimaBusqueda, paginaActual + 1);
    }
  });

  // === Búsqueda en tiempo real ===
  let timer;
  buscador.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      cargarCursos(buscador.value.trim(), 1);
    }, 300);
  });

  // === Carga inicial ===
  cargarCursos();
});