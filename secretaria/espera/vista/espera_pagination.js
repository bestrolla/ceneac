document.addEventListener('DOMContentLoaded', () => {
  const tabla = document.getElementById('tabla-espera');
  const buscador = document.getElementById('buscar-espera');
  const paginacion = document.getElementById('paginacion-espera');

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
      tabla.innerHTML = `<tr><td colspan="6">No se encontraron estudiantes en espera</td></tr>`;
      return;
    }
    tabla.innerHTML = estudiantes.map(e => `
      <tr data-cedula="${e.cedula}">
        <td>${escapeHtml(e.cedula)}</td>
        <td>${escapeHtml(e.nombre)}</td>
        <td>${escapeHtml(e.apellido)}</td>
        <td>${escapeHtml(e.telefono)}</td>
        <td>${escapeHtml(e.nombre_curso || 'N/A')}</td>
        <td class="icons">
          <button
            class="btn-action btn-move-lobby"
            data-cedula="${escapeHtml(e.cedula)}"
            data-curso="${escapeHtml(e.id_curso || '')}"
            title="Aprobar"
          >
            <img src="img/person-fill-add.svg" alt="aprobar" />
          </button>
          <button
            class="btn-action btn-delete"
            data-cedula="${escapeHtml(e.cedula)}"
            data-curso="${escapeHtml(e.id_curso || '')}"
            data-nombre-curso="${escapeHtml(e.nombre_curso || '')}"
            title="Eliminar"
          >
            <img src="img/trash3-fill.svg" alt="Eliminar" />
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

  // === Cargar estudiantes en espera ===
  function cargarEspera(query = '', page = 1) {
    fetch(`../logica/buscar_espera.php?q=${encodeURIComponent(query)}&page=${page}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          tabla.innerHTML = `<tr><td colspan="6">Error al cargar datos</td></tr>`;
          return;
        }
        renderTabla(data.data);
        renderPaginacion(data.currentPage, data.pages);
        paginaActual = data.currentPage;
        ultimaBusqueda = query;
      })
      .catch(() => tabla.innerHTML = `<tr><td colspan="6">Error de conexión</td></tr>`);
  }

  // === Paginación ===
  paginacion.addEventListener('click', e => {
    if (e.target.id === 'pagina-anterior' && paginaActual > 1) {
      cargarEspera(ultimaBusqueda, paginaActual - 1);
    }
    if (e.target.id === 'pagina-siguiente') {
      cargarEspera(ultimaBusqueda, paginaActual + 1);
    }
  });

  // === Búsqueda en tiempo real ===
  let timer;
  buscador.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      cargarEspera(buscador.value.trim(), 1);
    }, 300);
  });

  // === Carga inicial ===
  cargarEspera();
});