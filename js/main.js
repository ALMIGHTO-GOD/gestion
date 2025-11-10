document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Seleccionar Elementos ---
    const projectCards = document.querySelectorAll('.project-card');
    const modal = document.getElementById('project-modal');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    // Elementos del modal que vamos a rellenar
    const modalImage = document.getElementById('modal-image');
    const modalTitle = document.getElementById('modal-title');
    const modalStatus = document.getElementById('modal-status');
    const modalAuthor = document.getElementById('modal-author');
    const modalDescription = document.getElementById('modal-description');


    // --- 2. Función para mostrar el modal con datos ---
    function showModal(cardData) {
        // Rellenar los datos
        modalImage.src = cardData.image;
        modalTitle.textContent = cardData.title;
        modalAuthor.textContent = `Por: ${cardData.author}`;
        modalDescription.textContent = cardData.description;
        
        // Asignar el texto del estado
        modalStatus.textContent = cardData.status;

        // Limpiar clases de estado anteriores
        modalStatus.className = 'status-label'; 

        // Crear un nombre de clase CSS-friendly
        const statusClass = cardData.status.toLowerCase().replace(/ /g, '-');
        
        // Añadir la clase de color correcta
        modalStatus.classList.add(`status--${statusClass}`);

        // Mostrar el modal
        modal.classList.add('is-visible'); // <-- CAMBIADO
    }

    // --- 3. Función para ocultar el modal ---
    function hideModal() {
        modal.classList.remove('is-visible'); // <-- CAMBIADO
    }

    // --- 4. Asignar Eventos ---

    // Añadir un listener a CADA tarjeta
    projectCards.forEach(card => {
        card.addEventListener('click', () => {
            // 'dataset' lee todos los atributos 'data-' de la tarjeta clickeada
            showModal(card.dataset);
        });
    });

    // Evento para cerrar con el botón 'X'
    modalCloseBtn.addEventListener('click', hideModal);

    // Evento para cerrar haciendo clic FUERA del modal (en el overlay)
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            hideModal();
        }
    });

    // Opcional: Cerrar el modal con la tecla 'Escape'
    document.addEventListener('keydown', (event) => {
        // Solo cierra si la tecla es 'Escape' y el modal TIENE la clase 'is-visible'
        if (event.key === 'Escape' && modal.classList.contains('is-visible')) { // <-- CAMBIADO
            hideModal();
        }
    });

});
/* ---
   =============================================
   FILTRO DE BÚSQUEDA EN VIVO (Para Proyectos en Espera)
   =============================================
--- */

// Espera a que todo el HTML esté cargado antes de correr el script
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. "El Vigilante": Encuentra la barra de búsqueda en la página
    const searchBar = document.getElementById('search');

    // 2. Revisamos si la barra de búsqueda EXISTE en esta página
    //    (Esto evita errores en otras páginas como el dashboard)
    if (searchBar) {
        
        // 3. Encuentra los elementos con los que vamos a trabajar
        const countElement = document.getElementById('count');
        const projectGrid = document.querySelector('.project-grid');
        const projects = projectGrid.querySelectorAll('.project-card');

        // 4. "El Oído": Escucha cada vez que el usuario levanta una tecla
        searchBar.addEventListener('keyup', () => {
            
            // 5. "La Búsqueda": Agarra el texto y lo pone en minúsculas
            const searchText = searchBar.value.toLowerCase();
            let visibleCount = 0; // Un contador para el "Total:"

            // 6. "El Filtro": Recorre CADA tarjeta de proyecto
            projects.forEach(card => {
                
                // 7. Busca el texto DENTRO de la tarjeta (título y autor)
                const title = card.querySelector('h3').textContent.toLowerCase();
                const author = card.querySelector('.project-card__author').textContent.toLowerCase();
                
                // 8. "La Comparación": Revisa si el título O el autor incluyen el texto
                const isVisible = title.includes(searchText) || author.includes(searchText);
                
                // 9. "El Veredicto": Si coincide, la MUESTRA. Si no, la ESCONDE.
                card.style.display = isVisible ? 'block' : 'none';

                if (isVisible) {
                    visibleCount++; // Suma al contador si la tarjeta se ve
                }
            });

            // 10. "El Conteo": Actualiza el número de "Total:"
            if (countElement) {
                countElement.textContent = visibleCount;
            }
        });
    }
});