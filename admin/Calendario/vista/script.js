// Función para abrir/cerrar el sidebar
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar.style.width === "250px") {
    sidebar.style.width = "0";
  } else {
    sidebar.style.width = "250px";
  }
}

function renderCalendario(clases) {
  const contenedor = document.getElementById('calendario');
  if (!contenedor) return;

  // Agrupar clases por fecha
  const clasesPorFecha = {};
  clases.forEach(c => {
    if (!clasesPorFecha[c.fecha]) {
      clasesPorFecha[c.fecha] = [];
    }
    clasesPorFecha[c.fecha].push({curso: c.curso, horario: c.horario});
  });

  // Crear lista con fechas y sus clases
  const ul = document.createElement('ul');
  ul.style.listStyle = 'none';
  ul.style.padding = 0;

  Object.keys(clasesPorFecha).sort().forEach(fecha => {
    const liFecha = document.createElement('li');
    liFecha.style.marginBottom = '20px';

    const fechaTitulo = document.createElement('h3');
    fechaTitulo.textContent = new Date(fecha).toLocaleDateString('es-ES', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'});
    liFecha.appendChild(fechaTitulo);

    const listaClases = document.createElement('ul');
    listaClases.style.paddingLeft = '20px';

    clasesPorFecha[fecha].forEach(clase => {
      const liClase = document.createElement('li');
      liClase.textContent = `${clase.curso} — Horario: ${clase.horario}`;
      listaClases.appendChild(liClase);
    });

    liFecha.appendChild(listaClases);
    ul.appendChild(liFecha);
  });

  contenedor.appendChild(ul);
}

// Ejecutar al cargar página
document.addEventListener('DOMContentLoaded', () => {
  renderCalendario(clasesProgramadas);
});
