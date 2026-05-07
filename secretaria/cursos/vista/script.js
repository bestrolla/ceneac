
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      sidebar.style.width = (sidebar.style.width === "250px") ? "0" : "250px";
    }

    document.addEventListener("DOMContentLoaded", () => {
      const botones = document.querySelectorAll(".btn-lista-estudiantes");
      const modal = document.getElementById("modal-estudiantes");
      const cerrarModal = document.getElementById("modal-close");
      const listaEstudiantes = document.getElementById("lista-estudiantes-modal");

      botones.forEach((btn) => {
        btn.addEventListener("click", async () => {
          const idCurso = btn.dataset.cursoId;
          try {
            const response = await fetch(`../logica/obtener_estudiante.php?id_curso=${idCurso}`);
            const data = await response.json();

            listaEstudiantes.innerHTML = ""; // Limpiar contenido anterior

            if (data.success) {
              if (data.estudiantes.length > 0) {
                data.estudiantes.forEach(est => {
                  const li = document.createElement("li");
                  li.textContent = `${est.nombre} ${est.apellido} - C.I: ${est.cedula}`;
                  listaEstudiantes.appendChild(li);
                });
              } else {
                listaEstudiantes.innerHTML = "<li>No hay estudiantes aprobados en este curso.</li>";
              }
            } else {
              listaEstudiantes.innerHTML = `<li>${data.message}</li>`;
            }

            modal.style.display = "block";
          } catch (err) {
            console.error("Error:", err);
            listaEstudiantes.innerHTML = "<li>Error al cargar estudiantes.</li>";
            modal.style.display = "block";
          }
        });
      });

      cerrarModal.addEventListener("click", () => {
        modal.style.display = "none";
      });

      window.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.style.display = "none";
        }
      });
    });



