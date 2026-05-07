// calendar_script.js
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM para el calendario
    const mainContent = document.getElementById('main-content'); 
    const eventDetailsPanel = document.getElementById('event-details-panel'); 
    const footer = document.querySelector('.footer'); // Referencia al footer
    
    // Debug: verificar que mainContent existe
    if (!mainContent) {
        console.error('Element with ID "main-content" not found');
        return;
    }

    // Función para ajustar el contenido cuando el sidebar se abre/cierra
    function adjustContentForSidebar() {
        try {
            // Solo actualizar el calendario si existe
            if (typeof calendar !== 'undefined' && calendar && typeof calendar.updateSize === 'function') {
                setTimeout(() => {
                    calendar.updateSize();
                }, 100);
            }
        } catch (error) {
            console.error('Error adjusting content for sidebar:', error);
        }
    }

    // Compatibilidad con el sidebar centralizado
    // Observar cambios en el sidebar para ajustar el contenido del calendario
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    setTimeout(adjustContentForSidebar, 50);
                }
            });
        });
        observer.observe(sidebar, { attributes: true });
    }
    
    // Ajuste inicial del contenido
    setTimeout(adjustContentForSidebar, 500);




    // Function to display notification messages
    function showMessage(message, type = 'info') {
        const messageContainer = document.getElementById('message-container');
        if (!messageContainer) return;

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} rounded-md p-3 mb-2 shadow-md`;
        alertDiv.textContent = message;

        messageContainer.appendChild(alertDiv);

        // Remove the message after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Function to display a custom confirmation modal
    function showConfirmModal(message, onConfirm) {
        let overlay = document.getElementById('confirm-modal-overlay');
        let messageElement = document.getElementById('confirm-modal-message');
        let confirmBtn = document.getElementById('confirm-ok-btn');
        let cancelBtn = document.getElementById('confirm-cancel-btn');

        // Create the modal HTML if it doesn't exist
        if (!overlay) {
            const confirmModalHtml = `
                <div id="confirm-modal-overlay" class="confirm-modal-overlay">
                    <div class="confirm-modal-content">
                        <p id="confirm-modal-message"></p>
                        <div class="confirm-modal-buttons">
                            <button id="confirm-ok-btn" class="confirm-ok">Confirmar</button>
                            <button id="confirm-cancel-btn" class="confirm-cancel">Cancelar</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', confirmModalHtml);
            // Re-get references after creation
            overlay = document.getElementById('confirm-modal-overlay');
            messageElement = document.getElementById('confirm-modal-message');
            confirmBtn = document.getElementById('confirm-ok-btn');
            cancelBtn = document.getElementById('confirm-cancel-btn');
        }

        if (!overlay || !messageElement || !confirmBtn || !cancelBtn) {
            console.error("Confirmation modal elements not found.");
            return;
        }

        messageElement.textContent = message;
        overlay.classList.add('visible');

        // Clear previous event listeners to prevent multiple calls
        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        confirmBtn = newConfirmBtn;
        cancelBtn = newCancelBtn;

        const handleConfirm = () => {
            onConfirm();
            overlay.classList.remove('visible');
        };

        const handleCancel = () => {
            overlay.classList.remove('visible');
        };

        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', handleCancel);
    }

    // DOM element references for the event details panel
    const closePanelBtn = document.getElementById('close-panel-btn');
    const eventList = document.getElementById('event-list');
    const eventSummary = document.getElementById('event-summary');
    const addEventBtn = document.getElementById('add-event-btn');
    const editEventBtn = document.getElementById('edit-event-btn');
    const deleteEventBtnPanel = document.getElementById('delete-event-btn-panel');

    // DOM element references for the event form modal
    const eventFormModalOverlay = document.getElementById('event-form-modal-overlay');
    const eventFormModalTitle = document.getElementById('event-form-modal-title');
    const eventForm = document.getElementById('event-form');
    const eventIdInput = document.getElementById('event-id');
    const eventTitleInput = document.getElementById('event-title-input');
    const eventStartInput = document.getElementById('event-start-input');
    const eventEndInput = document.getElementById('event-end-input');
    const eventDescriptionInput = document.getElementById('event-description-input');
    const eventTypeSelect = document.getElementById('event-type-select');
    const eventProfesorSelect = document.getElementById('event-profesor-select');
    const eventSalonSelect = document.getElementById('event-salon-select');
    const eventAllDayCheckbox = document.getElementById('event-all-day-checkbox');
    const cancelEventFormBtn = document.getElementById('cancel-event-form-btn');

    let calendar; // Variable for the FullCalendar instance
    let selectedEvent = null; // To store the selected event in the panel
    let currentSelectedDate = null; // To store the date selected in the calendar

    // Function to open the side panel
    function openEventDetailsPanel() {
        if (eventDetailsPanel) {
            eventDetailsPanel.classList.add('open');
            // No need for mainContent.classList.add('panel-open') here if panel always overlays
            if (calendar) {
                calendar.updateSize(); // Force FullCalendar to recalculate its size
            }
        }
    }

    // Function to close the side panel
    function closeEventDetailsPanel() {
        if (eventDetailsPanel) {
            eventDetailsPanel.classList.remove('open');
            // No need for mainContent.classList.remove('panel-open') here if panel always overlays
            resetPanelSelection();
            if (calendar) {
                calendar.updateSize(); // Force FullCalendar to recalculate its size
            }
        }
    }

    // Reset panel selection and buttons
    function resetPanelSelection() {
        selectedEvent = null;
        if (eventSummary) eventSummary.innerHTML = '<h4>Resumen del Evento</h4><p>Haz clic en un evento de la lista para ver su detalle.</p>';
        if (editEventBtn) editEventBtn.disabled = true;
        if (deleteEventBtnPanel) deleteEventBtnPanel.disabled = true;
        const currentSelected = eventList.querySelector('.event-list-item.selected');
        if (currentSelected) {
            currentSelected.classList.remove('selected');
        }
    }

    // Load professors into the modal select
    async function loadProfessors() {
        try {
            const response = await fetch('../logica/obtener_profesores.php');
            const data = await response.json();
            if (data.status === 'success' && data.professors) {
                eventProfesorSelect.innerHTML = '<option value="">Seleccionar Profesor</option>'; // Reset
                data.professors.forEach(prof => {
                    const option = document.createElement('option');
                    option.value = prof.id_profesor;
                    option.textContent = prof.nombre_completo;
                    eventProfesorSelect.appendChild(option);
                });
            } else {
                showMessage(`Error al cargar profesores: ${data.message || 'Error desconocido'}`, 'danger');
            }
        } catch (error) {
            console.error('Error fetching professors:', error);
            showMessage('Error de red al cargar profesores.', 'danger');
        }
    }

    // Load rooms into the modal select, now with status check
    async function loadSalones() {
        try {
            const response = await fetch('../logica/obtener_salones.php');
            const data = await response.json();
            console.log('Respuesta de obtener_salones.php (con estado):', data); 

            if (data.status === 'success' && data.salones) {
                eventSalonSelect.innerHTML = '<option value="">Seleccionar Salón</option>'; // Reset
                data.salones.forEach(salon => {
                    const option = document.createElement('option');
                    option.value = salon.id_salon; 
                    option.textContent = salon.nombre_salon;

                    // Check salon status
                    if (salon.estado_salon === 'ocupado') { // Assuming 'ocupado' means reserved
                        option.disabled = true;
                        option.classList.add('occupied-salon'); // Add class for styling
                        // Add title for tooltip message
                        option.title = `Salón ${salon.nombre_salon} ya reservado.`; 
                    }
                    eventSalonSelect.appendChild(option);
                });
            } else {
                showMessage(`Error al cargar salones: ${data.message || 'Error desconocido'}`, 'danger');
            }
        } catch (error) {
            console.error('Error fetching salones:', error);
            showMessage('Error de red al cargar salones.', 'danger');
        }
    }

    // Add event listener for salon select to show message if disabled option is clicked
    eventSalonSelect.addEventListener('mousedown', function(e) {
        // Use mousedown instead of click because disabled options don't trigger click
        if (e.target.disabled) {
            showMessage(e.target.title, 'info'); // Show the tooltip message
            e.preventDefault(); // Prevent the dropdown from opening
            e.stopPropagation(); // Stop event propagation
        }
    });


    // Function to validate class times
    function isValidClassTime(startTime, endTime, dayOfWeek) {
        // dayOfWeek: 0 for Sunday, 1 for Monday, ..., 6 for Saturday
        const startHour = startTime.getHours();
        const startMinute = startTime.getMinutes();
        const endHour = endTime.getHours();
        const endMinute = endTime.getMinutes();

        // Helper to check if a time falls within a slot
        const checkSlot = (sH, sM, eH, eM) => {
            // Check if start time is within the slot
            if (startHour < sH || (startHour === sH && startMinute < sM)) return false;
            // Check if end time is within the slot
            if (endHour > eH || (endHour === eH && endMinute > eM)) return false;
            // Check if the event fits entirely within the slot
            if (startTime.getTime() >= new Date(startTime.getFullYear(), startTime.getMonth(), startTime.getDate(), sH, sM).getTime() &&
                endTime.getTime() <= new Date(endTime.getFullYear(), endTime.getMonth(), endTime.getDate(), eH, eM).getTime()) {
                return true;
            }
            return false;
        };

        if (dayOfWeek >= 1 && dayOfWeek <= 5) { // Lunes a Viernes
            // Slot 1: 9 AM - 1 PM
            if (checkSlot(9, 0, 13, 0)) return true;
            // Slot 2: 1 PM - 5 PM
            if (checkSlot(13, 0, 17, 0)) return true;
        } else if (dayOfWeek === 6) { // Sábado
            // Slot 1: 8 AM - 1 PM
            if (checkSlot(8, 0, 13, 0)) return true;
            // Slot 2: 1 PM - 5 PM
            if (checkSlot(13, 0, 17, 0)) return true;
        }
        return false;
    }


    // Function to show the event form modal
    function showEventFormModal(eventInfo = null) {
        loadProfessors(); // Load professors every time the modal opens
        loadSalones();    // Load rooms every time the modal opens

        if (eventInfo) {
            // Edit mode
            eventFormModalTitle.textContent = 'Editar Evento';
            eventIdInput.value = eventInfo.id;
            eventTitleInput.value = eventInfo.title;
            // Use moment.js for consistent date formatting
            eventStartInput.value = moment(eventInfo.start).format('YYYY-MM-DDTHH:mm');
            eventEndInput.value = eventInfo.end ? moment(eventInfo.end).format('YYYY-MM-DDTHH:mm') : '';
            eventDescriptionInput.value = eventInfo.extendedProps.description || '';
            eventTypeSelect.value = eventInfo.extendedProps.tipo_evento || 'general';
            eventProfesorSelect.value = eventInfo.extendedProps.id_profesor_persona || '';
            eventSalonSelect.value = eventInfo.extendedProps.id_salon || '';
            eventAllDayCheckbox.checked = eventInfo.allDay;
        } else {
            // Add mode
            eventFormModalTitle.textContent = 'Agregar Nuevo Evento';
            eventForm.reset(); // Clear the form
            eventIdInput.value = '';
            // Pre-fill start date with the selected calendar date if available
            if (currentSelectedDate) {
                eventStartInput.value = moment(currentSelectedDate).format('YYYY-MM-DDTHH:mm');
            } else {
                eventStartInput.value = moment().format('YYYY-MM-DDTHH:mm'); // Current date and time
            }
        }
        eventFormModalOverlay.classList.add('visible');
    }

    // Function to hide the event form modal
    function hideEventFormModal() {
        eventFormModalOverlay.classList.remove('visible');
        eventForm.reset();
        eventIdInput.value = '';
    }

    // FullCalendar initialization
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Initial view by month
            locale: 'es', // Spanish language
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            editable: true, // Allows dragging and resizing events
            selectable: true, // Allows selecting date ranges
            eventStartEditable: true, // Allows dragging events
            eventDurationEditable: true, // Allows resizing events
            navLinks: true, // Allows clicking on day/week names
            dayMaxEvents: true, // When there are many events, shows "+X more"
            // Important for the calendar to expand in height within a flex container
            expandRows: true, 
            events: {
                url: '../logica/obtener_eventos.php', 
                method: 'GET',
                failure: function() {
                    showMessage('Error al cargar los eventos del calendario.', 'danger');
                }
            },
            eventDidMount: function(info) {
                // Customize event appearance if needed
                // info.el.style.borderColor = 'red';
            },
            dateClick: function(info) {
                // When clicking on a calendar day
                currentSelectedDate = info.dateStr; // Save the selected date
                openEventDetailsPanel();
                // Load events for the selected date in the side panel
                loadEventsForDate(info.dateStr);
            },
            eventClick: function(info) {
                // When clicking on an event
                selectedEvent = info.event; // Save the selected event
                openEventDetailsPanel();
                // Display event details in the panel summary
                displayEventSummary(selectedEvent);
                // Enable edit and delete buttons
                if (editEventBtn) editEventBtn.disabled = false;
                if (deleteEventBtnPanel) deleteEventBtnPanel.disabled = false;

                // Highlight the event in the list if already loaded
                const eventListItem = eventList.querySelector(`[data-event-id="${selectedEvent.id}"]`);
                if (eventListItem) {
                    resetPanelSelection(); // Clear previous selections
                    eventListItem.classList.add('selected');
                }
            },
            eventDrop: function(info) {
                // When an event is dragged and dropped
                updateEventOnServer(info.event);
            },
            eventResize: function(info) {
                // When an event is resized
                updateEventOnServer(info.event);
            }
        });
        calendar.render(); // Render the calendar

        // Call initial adjustments after calendar is rendered
        adjustContentForSidebar(); // Llamada inicial para ajustar el contenido si el sidebar está abierto
    } else {
        console.error("Element #calendar not found.");
    }

    // Event handlers for the side panel
    if (closePanelBtn) {
        closePanelBtn.addEventListener('click', closeEventDetailsPanel);
    }
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => showEventFormModal());
    }
    if (editEventBtn) {
        editEventBtn.addEventListener('click', () => {
            if (selectedEvent) {
                showEventFormModal(selectedEvent);
            } else {
                showMessage('Por favor, selecciona un evento para editar.', 'info');
            }
        });
    }
    if (deleteEventBtnPanel) {
        deleteEventBtnPanel.addEventListener('click', () => {
            if (selectedEvent) {
                showConfirmModal(`¿Estás seguro de que quieres eliminar el evento "${selectedEvent.title}"?`, () => {
                    deleteEventOnServer(selectedEvent.id);
                });
            } else {
                showMessage('Por favor, selecciona un evento para eliminar.', 'info');
            }
        });
    }

    // Event handlers for the form modal
    if (cancelEventFormBtn) {
        cancelEventFormBtn.addEventListener('click', hideEventFormModal);
    }
    
    // Add event listener for the close button (X)
    const closeEventModalBtn = document.getElementById('close-event-modal-btn');
    if (closeEventModalBtn) {
        closeEventModalBtn.addEventListener('click', hideEventFormModal);
    }
    
    if (eventFormModalOverlay) {
        eventFormModalOverlay.addEventListener('click', (e) => {
            if (e.target === eventFormModalOverlay) {
                hideEventFormModal();
            }
        });
    }

    // Handle event form submission
    if (eventForm) {
        eventForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const isEdit = eventIdInput.value !== '';
            const url = isEdit ? '../logica/update_event.php' : '../logica/create_event.php';
            const method = 'POST'; // Always POST for create/update

            const eventType = eventTypeSelect.value;
            const eventStart = new Date(eventStartInput.value);
            const eventEnd = eventEndInput.value ? new Date(eventEndInput.value) : null;

            // --- Validar horarios para tipo 'clase' ---
            if (eventType === 'clase') {
                if (!eventEnd) {
                    showMessage('Para eventos de tipo "Clase", la fecha y hora de fin son obligatorias.', 'danger');
                    return;
                }
                const dayOfWeek = eventStart.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
                if (!isValidClassTime(eventStart, eventEnd, dayOfWeek)) {
                    let errorMessage = 'Los horarios para las clases deben ser:';
                    errorMessage += '\nLunes a Viernes: 9am-1pm o 1pm-5pm.';
                    errorMessage += '\nSábados: 8am-1pm o 1pm-5pm.';
                    showMessage(errorMessage, 'danger');
                    return; // Stop submission
                }
            }
            // --- Fin de validación de horarios ---


            const eventData = {
                id: isEdit ? eventIdInput.value : undefined, // Only include ID if it's an edit
                title: eventTitleInput.value,
                start: moment(eventStartInput.value).format('YYYY-MM-DDTHH:mm:ss'),
                end: eventEndInput.value ? moment(eventEndInput.value).format('YYYY-MM-DDTHH:mm:ss') : null,
                description: eventDescriptionInput.value,
                tipo_evento: eventType, // Use the validated eventType
                // Convert to Number() explicitly here
                id_profesor_persona: eventProfesorSelect.value ? Number(eventProfesorSelect.value) : null,
                id_salon: eventSalonSelect.value ? Number(eventSalonSelect.value) : null,
                allDay: eventAllDayCheckbox.checked ? 1 : 0, // Send as 0 or 1
                // Color can be defined here if not coming from DB
                // color: '#2196F3' // Example: default color
            };

            // DEBUG: Log the eventData being sent for update
            console.log('Sending event data:', eventData);

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(eventData)
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showMessage(result.message, 'success');
                    hideEventFormModal();
                    calendar.refetchEvents(); // Reload events in the calendar
                    if (currentSelectedDate) {
                        loadEventsForDate(currentSelectedDate); // Reload events in the panel
                    }
                } else {
                    showMessage(`Error: ${result.message}`, 'danger');
                }
            } catch (error) {
                console.error('Error saving event:', error);
                showMessage('Error de red al guardar el evento.', 'danger');
            }
        });
    }

    // Function to load events for a specific date in the side panel
    async function loadEventsForDate(dateStr) {
        eventList.innerHTML = '<p>Cargando eventos...</p>'; // Loading message
        resetPanelSelection(); // Clear selection when loading new date
        try {
            const response = await fetch(`../logica/obtener_eventos.php?date=${dateStr}`);
            const events = await response.json(); // Attempt to parse as JSON

            // DEBUG: Log the raw response from obtener_eventos.php
            console.log('Raw events response from obtener_eventos.php:', events);

            if (Array.isArray(events)) { // Check if the response is an array
                eventList.innerHTML = ''; // Clear list
                if (events.length === 0) {
                    eventList.innerHTML = '<p>No hay eventos para esta fecha.</p>';
                } else {
                    events.forEach(event => {
                        const listItem = document.createElement('div');
                        listItem.className = 'event-list-item';
                        listItem.setAttribute('data-event-id', event.id); // Save ID for selection
                        listItem.innerHTML = `
                            <span class="event-title">${event.title}</span>
                            <span class="event-time">${moment(event.start).format('HH:mm')} - ${event.end ? moment(event.end).format('HH:mm') : 'Todo el día'}</span>
                        `;
                        listItem.addEventListener('click', () => {
                            resetPanelSelection(); // Clear previous selections
                            listItem.classList.add('selected');
                            // Find the full event in the calendar to pass to displayEventSummary
                            const fullCalendarEvent = calendar.getEventById(event.id);
                            if (fullCalendarEvent) {
                                selectedEvent = fullCalendarEvent;
                                displayEventSummary(fullCalendarEvent);
                                if (editEventBtn) editEventBtn.disabled = false;
                                if (deleteEventBtnPanel) deleteEventBtnPanel.disabled = false;
                            }
                        });
                        eventList.appendChild(listItem);
                    });
                }
            } else if (events && events.status === 'error') {
                // If the response is a JSON object with status 'error'
                eventList.innerHTML = `<p style="color: red;">Error al cargar eventos: ${events.message}</p>`;
                showMessage(`Error al cargar eventos: ${events.message}`, 'danger');
            } else {
                // If the response is neither an array nor an expected error object
                eventList.innerHTML = `<p style="color: red;">Error inesperado al cargar eventos. Formato de datos incorrecto.</p>`;
                showMessage('Error inesperado al cargar eventos. Por favor, revisa la consola para más detalles.', 'danger');
                console.error('Unexpected response from obtener_eventos.php:', events);
            }

        } catch (error) {
            console.error('Error loading events for the date:', error);
            eventList.innerHTML = `<p style="color: red;">Error de red al cargar eventos.</p>`;
            showMessage('Error de red al cargar eventos para la fecha seleccionada.', 'danger');
        }
    }

    // Function to display event summary in the panel
    function displayEventSummary(event) {
        if (!event || !eventSummary) {
            if (eventSummary) eventSummary.innerHTML = '<h4>Resumen del Evento</h4><p>Haz clic en un evento de la lista para ver su detalle.</p>';
            return;
        }

        let summaryHtml = `<h4>${event.title}</h4>`;
        summaryHtml += `<p><strong>Inicio:</strong> ${moment(event.start).format('DD/MM/YYYY HH:mm')}</p>`;
        if (event.end) {
            summaryHtml += `<p><strong>Fin:</strong> ${moment(event.end).format('DD/MM/YYYY HH:mm')}</p>`;
        } else if (event.allDay) {
            summaryHtml += `<p><strong>Duración:</strong> Todo el día</p>`;
        }

        if (event.extendedProps.description) {
            summaryHtml += `<p><strong>Descripción:</strong> ${event.extendedProps.description}</p>`;
        }
        if (event.extendedProps.tipo_evento) {
            summaryHtml += `<p><strong>Tipo:</strong> ${event.extendedProps.tipo_evento}</p>`;
        }
        // Show professor and room name if available
        if (event.extendedProps.profesor_nombre) {
            summaryHtml += `<p><strong>Profesor:</strong> ${event.extendedProps.profesor_nombre}</p>`;
        }
        if (event.extendedProps.salon_nombre) {
            summaryHtml += `<p><strong>Salón:</strong> ${event.extendedProps.salon_nombre}</p>`;
        }
        // Ensure extendedProps.clases_tomadas and total_clases exist before attempting to access
        if (event.extendedProps && event.extendedProps.clases_tomadas !== undefined && event.extendedProps.total_clases !== undefined) {
             summaryHtml += `<p><strong>Clases:</strong> ${event.extendedProps.clases_tomadas} / ${event.extendedProps.total_clases}</p>`;
        }
        if (event.extendedProps.is_rescheduled) {
            summaryHtml += `<p><strong>Reprogramado:</strong> Sí</p>`;
        }
        if (event.extendedProps.class_number) {
            summaryHtml += `<p><strong>Número de Clase:</strong> ${event.extendedProps.class_number}</p>`;
        }
        if (event.extendedProps.class_dates_json) {
            // Assuming class_dates_json is an array, join it
            summaryHtml += `<p><strong>Fechas de Clase:</strong> ${event.extendedProps.class_dates_json.join(', ')}</p>`;
        }


        eventSummary.innerHTML = summaryHtml;
    }

    // Function to update an event on the server (drag and resize)
    async function updateEventOnServer(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            start: moment(event.start).format('YYYY-MM-DDTHH:mm:ss'),
            end: event.end ? moment(event.end).format('YYYY-MM-DDTHH:mm:ss') : null,
            allDay: event.allDay ? 1 : 0,
            description: event.extendedProps.description || null,
            tipo_evento: event.extendedProps.tipo_evento || 'general',
            id_profesor_persona: event.extendedProps.id_profesor_persona || null,
            id_salon: event.extendedProps.id_salon || null,
            color: event.backgroundColor || null,
            clases_tomadas: event.extendedProps.clases_tomadas || 0,
            total_clases: event.extendedProps.total_clases || 0,
            is_rescheduled: event.extendedProps.is_rescheduled ? 1 : 0,
            original_course_id: event.extendedProps.original_course_id || null,
            class_number: event.extendedProps.class_number || null,
            class_dates_json: event.extendedProps.class_dates_json || null
        };

        // DEBUG: Log the eventData being sent for update
        console.log('Sending event update data:', eventData);

        try {
            const response = await fetch('../logica/update_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(eventData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showMessage(result.message, 'success');
                // No need to refetchEvents here, FullCalendar already updated visually
                if (currentSelectedDate) {
                    loadEventsForDate(currentSelectedDate); // Reload events in the panel
                }
            } else {
                showMessage(`Error al actualizar evento: ${result.message}`, 'danger');
                // If there's an error, revert the event visually
                event.revert();
            }
        } catch (error) {
            console.error('Error de red al actualizar evento:', error);
            showMessage('Error de red al actualizar el evento.', 'danger');
            event.revert(); // Revert the event if there's a network error
        }
    }

    // Function to delete an event on the server
    async function deleteEventOnServer(eventId) {
        try {
            const response = await fetch('../logica/delete_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: eventId })
            });
            const result = await response.json();

            if (result.status === 'success') {
                showMessage(result.message, 'success');
                calendar.refetchEvents(); // Reload events in the calendar
                closeEventDetailsPanel(); // Close the panel after deleting
            } else {
                showMessage(`Error al eliminar evento: ${result.message}`, 'danger');
            }
        } catch (error) {
            console.error('Error de red al eliminar evento:', error);
            showMessage('Error de red al eliminar el evento.', 'danger');
        }
    }

    // Ejecutar ajustes iniciales al cargar la ventana y al redimensionar
    window.addEventListener('load', adjustContentForSidebar);
    window.addEventListener('resize', adjustContentForSidebar);
});
