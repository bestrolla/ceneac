document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-secre');

  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
  }
  function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    toastContainer.appendChild(t);
    setTimeout(() => t.remove(), 4000);
  }

  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const fd = new FormData(form);
      fetch('../logica/agregar_secretaria.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast('✅ ' + data.message, 'success');
            form.reset();
            setTimeout(() => location.reload(), 1500);
          } else {
            showToast('⚠️ ' + data.message, 'error');
          }
        })
        .catch(() => showToast('❌ Error al agregar secretaria', 'error'));
    });
  }
});
