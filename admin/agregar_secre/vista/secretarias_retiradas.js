document.addEventListener('DOMContentLoaded', () => {
  const tabla = document.getElementById('tabla-retiradas');
  const buscador = document.getElementById('buscar-secre-ret');
  const paginacion = document.getElementById('paginacion-ret');

  // Modales
  const modalReintegrar = document.getElementById('modal-reintegrar');
  const cerrarModalReintegrar = document.getElementById('cerrar-modal-reintegrar');
  const cancelarReintegrar = document.getElementById('cancelar-reintegrar');
  const formReintegrar = document.getElementById('form-reintegrar-secre');

  let paginaActual = 1;
  let ultimaBusqueda = '';
  let idSecreReintegrar = null;

  // === TOAST SYSTEM ===
  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
  }
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
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
  function renderTabla(secretarias) {
  if (!secretarias || secretarias.length === 0) {
    tabla.innerHTML = `<tr><td colspan="7">No se encontraron secretarias retiradas</td></tr>`;
    return;
  }
  tabla.innerHTML = secretarias.map(s => `
    <tr data-id="${s.id_secre}">
      <td>${escapeHtml(s.nombre)} ${escapeHtml(s.apellido)}</td>
      <td>${escapeHtml(s.cedula)}</td>
      <td>${escapeHtml(s.telefono)}</td>
      <td>${escapeHtml(s.correo)}</td>
      <td>${escapeHtml(s.razon ?? '')}</td>
      <td>${escapeHtml(s.fecha_retiro ?? '')}</td> <!-- 🔥 Ahora sí -->
      <td>
        <button class="btn-reintegrar" data-id="${s.id_secre}">Reintegrar</button>
      </td>
    </tr>
  `).join('');
   }


  // === Render paginación ===
  function renderPaginacion(current, total) {
    paginacion.innerHTML = `
      <button id="pagina-anterior" ${current <= 1 ? 'disabled' : ''}>Anterior</button>
      <span>Página ${current} de ${total}</span>
      <button id="pagina-siguiente" ${current >= total ? 'disabled' : ''}>Siguiente</button>
    `;
  }

  // === Cargar secretarias retiradas ===
  function cargarRetiradas(query = '', page = 1) {
    fetch(`../logica/buscar_secretarias_retiradas.php?q=${encodeURIComponent(query)}&page=${page}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          tabla.innerHTML = `<tr><td colspan="7">Error al cargar datos</td></tr>`;
          return;
        }
        renderTabla(data.data);
        renderPaginacion(data.currentPage, data.pages);
        paginaActual = data.currentPage;
        ultimaBusqueda = query;
      })
      .catch(() => tabla.innerHTML = `<tr><td colspan="7">Error de conexión</td></tr>`);
  }

  // === Clicks en la tabla ===
  tabla.addEventListener('click', e => {
    const id = e.target.dataset.id;

    // Reintegrar
    if (e.target.classList.contains('btn-reintegrar')) {
      idSecreReintegrar = id;
      document.getElementById('reintegrar-id-secre').value = id;
      modalReintegrar.style.display = 'flex';
    }
  });

  // === Reintegrar secretaria ===
  formReintegrar.addEventListener('submit', e => {
    e.preventDefault();

    fetch('../logica/reintegrar_secretaria.php', {
      method: 'POST',
      body: new URLSearchParams({ id_secre: idSecreReintegrar })
    })
      .then(res => res.json())
      .then(data => {
        modalReintegrar.style.display = 'none';
        if (data.success) {
          showToast('✅ Secretaria reintegrada', 'success');
          cargarRetiradas(ultimaBusqueda, paginaActual);
        } else {
          showToast(`⚠️ ${data.message}`, 'error');
        }
      })
      .catch(() => showToast('❌ Error al reintegrar', 'error'));
  });

  // === Cerrar modales ===
  cerrarModalReintegrar.addEventListener('click', () => modalReintegrar.style.display = 'none');
  cancelarReintegrar.addEventListener('click', () => modalReintegrar.style.display = 'none');

  // === Paginación ===
  paginacion.addEventListener('click', e => {
    if (e.target.id === 'pagina-anterior' && paginaActual > 1) {
      cargarRetiradas(ultimaBusqueda, paginaActual - 1);
    }
    if (e.target.id === 'pagina-siguiente') {
      cargarRetiradas(ultimaBusqueda, paginaActual + 1);
    }
  });

  // === Búsqueda en tiempo real ===
  let timer;
  buscador.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      cargarRetiradas(buscador.value.trim(), 1);
    }, 300);
  });

  // === Carga inicial
  cargarRetiradas();
});
