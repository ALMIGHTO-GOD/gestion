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