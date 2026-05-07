document.addEventListener('DOMContentLoaded', () => {
  const formSecre = document.getElementById('form-secre');
  const tabla = document.getElementById('tabla-secretarias');
  const buscador = document.getElementById('buscar-secre');
  const paginacion = document.getElementById('paginacion-secre');

  // Modales
  const modalEditar = document.getElementById('modal-editar');
  const cerrarModalEditar = document.getElementById('cerrar-modal');
  const modalRetirar = document.getElementById('modal-retirar'); // nuevo modal retirar
  const cerrarModalRetirar = document.getElementById('cerrar-modal-retirar');
  const btnCancelarRetirar = document.getElementById('cancelar-retirar');
  const formRetirar = document.getElementById('form-retirar-secre');
  const modalEstado = document.getElementById('modal-estado');
  const cerrarModalEstado = document.getElementById('cerrar-modal-estado');
  const modalEstadoDetalle = document.getElementById('modal-estado-detalle');
  const cerrarModalEstadoDetalle = document.getElementById('cerrar-modal-estado-detalle');

  const formEditar = document.getElementById('form-editar-secre');
  const formEstado = document.getElementById('form-estado-secre');

  let paginaActual = 1;
  let ultimaBusqueda = '';
  let idSecreRetirar = null;

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
      tabla.innerHTML = `<tr><td colspan="4">No se encontraron secretarias</td></tr>`;
      return;
    }
    tabla.innerHTML = secretarias.map(s => `
      <tr data-id="${s.id_secre}">
        <td>${escapeHtml(s.nombre)} ${escapeHtml(s.apellido)}</td>
        <td>${escapeHtml(s.cedula)}</td>
        <td class="celda-estado estado-${escapeHtml(s.status.toLowerCase())}" 
            data-id="${s.id_secre}" 
            style="cursor:pointer; text-decoration:underline;">
          ${escapeHtml(s.status)}
        </td>
        <td>
          <button class="btn-info" data-id="${s.id_secre}">Info</button>
          <button class="btn-editar" data-id="${s.id_secre}">Editar</button>
          <button class="btn-retirar" data-id="${s.id_secre}">Retirar</button>
          <button class="btn-estado" data-id="${s.id_secre}">Cambiar</button>
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

  // === Cargar secretarias ===
  function cargarSecretarias(query = '', page = 1) {
    fetch(`../logica/buscar_secretarias.php?q=${encodeURIComponent(query)}&page=${page}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          tabla.innerHTML = `<tr><td colspan="4">Error al cargar datos</td></tr>`;
          return;
        }
        renderTabla(data.data);
        renderPaginacion(data.currentPage, data.pages);
        paginaActual = data.currentPage;
        ultimaBusqueda = query;
      })
      .catch(() => tabla.innerHTML = `<tr><td colspan="4">Error de conexión</td></tr>`);
  }

  // === Clicks en la tabla ===
  tabla.addEventListener('click', e => {
    const id = e.target.dataset.id;

    // Info
    if (e.target.classList.contains('btn-info')) {
      fetch(`../logica/obtener_secretarias.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            mostrarInfoSecretaria(
              data.id_secre,
              data.nombre,
              data.apellido,
              data.cedula,
              data.telefono,
              data.correo,
              data.fecha_registro,
              data.status
            );
          } else {
            showToast('⚠️ No se encontraron datos', 'error');
          }
        })
        .catch(() => showToast('❌ Error al cargar datos', 'error'));
    }

    // Editar
    if (e.target.classList.contains('btn-editar')) {
      fetch(`../logica/obtener_secretarias.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('edit-id-secre').value = data.id_secre ?? '';
            document.getElementById('edit-nombre').value = data.nombre ?? '';
            document.getElementById('edit-apellido').value = data.apellido ?? '';
            document.getElementById('edit-telefono').value = data.telefono ?? '';
            document.getElementById('edit-correo').value = data.correo ?? '';
            modalEditar.style.display = 'flex';
          } else {
            showToast('⚠️ No se encontraron datos', 'error');
          }
        })
        .catch(() => showToast('❌ Error al cargar datos', 'error'));
    }

    // Retirar
    if (e.target.classList.contains('btn-retirar')) {
      idSecreRetirar = id;
      document.getElementById('retirar-id-secre').value = idSecreRetirar;
      modalRetirar.style.display = 'flex';
    }

    // Detalle estado
    if (e.target.classList.contains('celda-estado')) {
      fetch(`../logica/obtener_secretarias.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('det-nombre').textContent = `${escapeHtml(data.nombre)} ${escapeHtml(data.apellido)}`;
            document.getElementById('det-status').textContent = escapeHtml(data.status);
            document.getElementById('det-razon').textContent = escapeHtml(data.razon);
            document.getElementById('det-fecha').textContent = escapeHtml(data.fecha);
            modalEstadoDetalle.style.display = 'flex';
          } else {
            showToast('⚠️ No se pudo obtener detalle del estado', 'error');
          }
        })
        .catch(() => showToast('❌ Error al cargar detalles', 'error'));
    }

    // Abrir modal cambiar estado
    if (e.target.classList.contains('btn-estado')) {
      document.getElementById('estado-id-secre').value = id;
      modalEstado.style.display = 'flex';
    }
  });

  // === Guardar cambios (Editar)
  formEditar.addEventListener('submit', e => {
    e.preventDefault();
    const fd = new FormData(formEditar);

    fetch('../logica/editar_secretaria.php', { method: 'POST', body: fd })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          modalEditar.style.display = 'none';
          showToast('✅ Secretaria actualizada', 'success');
          cargarSecretarias(ultimaBusqueda, paginaActual);
        } else {
          showToast(`⚠️ ${data.message}`, 'error');
        }
      })
      .catch(() => showToast('❌ Error al actualizar', 'error'));
  });

  // === Guardar cambios (Estado)
  formEstado.addEventListener('submit', e => {
    e.preventDefault();
    const fd = new FormData(formEstado);

    fetch('../logica/cambiar_estado_secretaria.php', { method: 'POST', body: fd })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          modalEstado.style.display = 'none';
          showToast('✅ Estado actualizado', 'success');
          cargarSecretarias(ultimaBusqueda, paginaActual);
        } else {
          showToast(`⚠️ ${data.message}`, 'error');
        }
      })
      .catch(() => showToast('❌ Error al cambiar estado', 'error'));
  });

// === Guardar cambios (Retirar)
 formRetirar.addEventListener('submit', e => {
  e.preventDefault();
  const fd = new FormData(formRetirar);
  const idSecre = document.getElementById('retirar-id-secre').value;

  fetch('../logica/retirar_secretaria.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
      modalRetirar.style.display = 'none';
      if (data.success) {
        showToast('✅ Secretaria retirada', 'success');

        // 🔥 Quitar la fila de la tabla inmediatamente
        const fila = tabla.querySelector(`tr[data-id="${idSecre}"]`);
        if (fila) fila.remove();

        // Y recargar desde servidor para asegurar sincronización
        cargarSecretarias(ultimaBusqueda, paginaActual);
      } else {
        showToast(`⚠️ ${data.message}`, 'error');
      }
    })
    .catch(() => showToast('❌ Error al retirar', 'error'));
 });

  // === Cerrar modales
  cerrarModalEditar.addEventListener('click', () => modalEditar.style.display = 'none');
  cerrarModalEstado.addEventListener('click', () => modalEstado.style.display = 'none');
  cerrarModalEstadoDetalle.addEventListener('click', () => modalEstadoDetalle.style.display = 'none');
  btnCancelarRetirar.addEventListener('click', () => modalRetirar.style.display = 'none');
  cerrarModalRetirar.addEventListener('click', () => modalRetirar.style.display = 'none');

  // === Paginación
  paginacion.addEventListener('click', e => {
    if (e.target.id === 'pagina-anterior' && paginaActual > 1) {
      cargarSecretarias(ultimaBusqueda, paginaActual - 1);
    }
    if (e.target.id === 'pagina-siguiente') {
      cargarSecretarias(ultimaBusqueda, paginaActual + 1);
    }
  });

  // === Búsqueda en tiempo real
  let timer;
  buscador.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      cargarSecretarias(buscador.value.trim(), 1);
    }, 300);
  });

  // === Agregar secretaria
  if (formSecre) {
    formSecre.addEventListener('submit', e => {
      e.preventDefault();
      const fd = new FormData(formSecre);
      fetch('../logica/agregar_secretaria.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showToast('✅ Secretaria agregada correctamente', 'success');
            formSecre.reset();
            cargarSecretarias();
          } else {
            showToast(`⚠️ ${data.message}`, 'error');
          }
        })
        .catch(() => showToast('❌ Error al agregar secretaria', 'error'));
    });
  }

  // === Carga inicial
  cargarSecretarias();
});
