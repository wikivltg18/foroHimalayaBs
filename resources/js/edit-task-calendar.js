// resources/js/edit-task-calendar.js
// Manejo de calendario y selección de horarios en editar tarea

import * as bootstrap from 'bootstrap';
import { initCalendarModal } from './calendar-modal.js';

// Inicializar modal de calendario
document.addEventListener('DOMContentLoaded', function () {
    const eventsUrl = document.querySelector('[data-events-url]')?.dataset.eventsUrl || '/agenda/events';
    const resourcesUrl = document.querySelector('[data-resources-url]')?.dataset.resourcesUrl || '/agenda/resources';

    initCalendarModal('taskCalendarModal', eventsUrl, resourcesUrl, 'onTaskCalendarSelect');
});

export function initializeEditTaskCalendar() {
    console.log('[EditCalendar] Inicializando calendario en editar tarea');

    const elements = {
        usuarioSelect: document.getElementById('usuario_id'),
        tiempoEstimadoInput: document.querySelector('input[name="tiempo_estimado_h"]'),
        selectedStartTimeInput: document.getElementById('selectedStartTime'),
        selectedUserIdInput: document.getElementById('selectedUserId'),
        calendarModal: document.getElementById('taskCalendarModal'),
        selectedTimeDisplay: document.getElementById('selectedTimeDisplay'),
        selectedTimeText: document.getElementById('selectedTimeText'),
        calendarButtonContainer: document.getElementById('calendarButtonContainer'),
    };

    console.log('[EditCalendar] Elementos encontrados:', {
        usuarioSelect: !!elements.usuarioSelect,
        tiempoEstimadoInput: !!elements.tiempoEstimadoInput,
        calendarButtonContainer: !!elements.calendarButtonContainer,
        calendarModal: !!elements.calendarModal,
    });

    // Verificar que existan los elementos básicos
    if (!elements.usuarioSelect || !elements.tiempoEstimadoInput) {
        console.warn('[EditCalendar] Elementos críticos no encontrados');
        return;
    }

    /**
     * Cargar calendarios del usuario autenticado
     */
    async function loadUserCalendars() {
        try {
            const response = await fetch('/ajax/google/calendars', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('HTTP ' + response.status);

            const data = await response.json();
            renderCalendars(data);
        } catch (e) {
            console.error('Error cargando calendarios:', e);
            renderCalendarsError();
        }
    }

    /**
     * Renderizar selector de calendarios
     */
    function renderCalendars(data) {
        const container = document.getElementById('googleCalendarSelection');
        if (!container) return;

        if (!data.connected) {
            container.innerHTML = `
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Google Calendar no conectado</strong>
                    <p class="mb-0 mt-2">
                        <a href="/google/calendars" class="alert-link">
                            Conecta tu cuenta de Google Calendar aquí
                        </a>
                    </p>
                </div>
            `;
            return;
        }

        // Obtener calendario actualmente seleccionado desde el input oculto
        let selectedCalendar = document.getElementById('selectedGoogleCalendar')?.value || data.default_calendar || 'primary';

        // Intentar auto-seleccionar calendario basándose en email del colaborador
        const usuarioSelect = document.getElementById('usuario_id');
        if (usuarioSelect && usuarioSelect.value) {
            const selectedOption = usuarioSelect.options[usuarioSelect.selectedIndex];
            const userEmail = selectedOption?.dataset?.email;

            if (userEmail) {
                const matchingCalendar = data.calendars.find(cal =>
                    cal.id.toLowerCase() === userEmail.toLowerCase() ||
                    cal.summary.toLowerCase().includes(userEmail.toLowerCase())
                );

                if (matchingCalendar) {
                    selectedCalendar = matchingCalendar.id;
                    console.log('[EditCalendar] Auto-seleccionado calendario:', matchingCalendar.summary, 'para usuario:', userEmail);
                }
            }
        }

        const calendarOptions = data.calendars
            .map(cal => `
                <option value="${cal.id}" data-primary="${cal.primary ? 'true' : 'false'}" 
                        ${selectedCalendar === cal.id ? 'selected' : ''}>
                    ${cal.summary}
                    ${cal.primary ? ' (Principal)' : ''}
                    ${cal.description ? ` - ${cal.description}` : ''}
                </option>
            `)
            .join('');

        container.innerHTML = `
            <label class="form-label fw-bold">
                <i class="bi bi-calendar-check me-2"></i>Calendario de Google
            </label>
            <select class="form-select mb-3" id="googleCalendarSelect">
                <option disabled>Seleccionar calendario...</option>
                ${calendarOptions}
            </select>
            <small class="text-muted">
                Selecciona en qué calendario de Google se sincronizará el evento
            </small>
        `;

        // Guardar selección en input oculto
        document.getElementById('googleCalendarSelect')?.addEventListener('change', (e) => {
            document.getElementById('selectedGoogleCalendar').value = e.target.value;
        });

        // Establecer valores
        document.getElementById('selectedGoogleCalendar').value = selectedCalendar;
        if (document.getElementById('googleCalendarSelect')) {
            document.getElementById('googleCalendarSelect').value = selectedCalendar;
        }
    }

    /**
     * Renderizar error de calendarios
     */
    function renderCalendarsError() {
        const container = document.getElementById('googleCalendarSelection');
        if (!container) return;

        container.innerHTML = `
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Error al obtener calendarios</strong>
                <p class="mb-0 mt-2">No se pudo cargar la lista de calendarios. Intenta reconectar tu cuenta.</p>
            </div>
        `;
    }

    /**
     * Mostrar/ocultar botón de ver calendario
     */
    function updateCalendarButton() {
        const usuarioId = elements.usuarioSelect?.value || '';
        const tiempoEstimado = parseFloat(elements.tiempoEstimadoInput?.value) || 0;
        const shouldShow = usuarioId && tiempoEstimado > 0;

        console.log('[EditCalendar] updateCalendarButton:', { usuarioId, tiempoEstimado, shouldShow });

        if (elements.calendarButtonContainer) {
            elements.calendarButtonContainer.style.display = shouldShow ? 'block' : 'none';
        }
    }

    /**
     * Mostrar horario seleccionado
     */
    function displaySelectedTime(startTime, endTime) {
        if (!elements.selectedTimeDisplay || !elements.selectedTimeText) return;

        const start = new Date(startTime);
        const end = new Date(endTime);

        const timeStr = `${start.toLocaleString('es-ES', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })} - ${end.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        })}`;

        elements.selectedTimeText.textContent = timeStr;
        elements.selectedTimeDisplay.style.display = 'block';
    }

    /**
     * Callback cuando se selecciona un slot en el calendario
     */
    window.onTaskCalendarSelect = function (data) {
        const tiempoEstimado = parseFloat(elements.tiempoEstimadoInput.value) || 1;

        // Parsear fecha-hora ISO
        const start = new Date(data.startStr);
        const end = new Date(start.getTime() + tiempoEstimado * 60 * 60 * 1000);

        // Guardar selección con formato correcto
        if (elements.selectedStartTimeInput) {
            elements.selectedStartTimeInput.value = start.toISOString();
        }
        if (elements.selectedUserIdInput) {
            elements.selectedUserIdInput.value = data.resourceId;
        }

        // Mostrar confirmación visual
        displaySelectedTime(start, end);

        showNotification(
            `✅ Horario seleccionado: ${start.toLocaleTimeString('es-ES')} - ${end.toLocaleTimeString('es-ES')}`,
            'success'
        );

        // Cerrar modal
        if (elements.calendarModal) {
            const modal = bootstrap.Modal.getInstance(elements.calendarModal);
            if (modal) modal.hide();
        }
    };

    /**
     * Mostrar notificación
     */
    function showNotification(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.form-card');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);

            // Auto-dismiss después de 5 segundos
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }, 5000);
        }
    }

    /**
     * Event listeners
     */
    if (elements.usuarioSelect) {
        elements.usuarioSelect.addEventListener('change', (e) => {
            console.log('[EditCalendar] Usuario seleccionado:', e.target.value);
            updateCalendarButton();
            // Recargar calendarios para auto-seleccionar el del colaborador
            loadUserCalendars();
        });
    }

    if (elements.tiempoEstimadoInput) {
        elements.tiempoEstimadoInput.addEventListener('input', (e) => {
            console.log('[EditCalendar] Tiempo estimado:', e.target.value);
            updateCalendarButton();
        });
    }

    const viewCalendarBtn = document.getElementById('btnViewCalendar');
    if (viewCalendarBtn) {
        viewCalendarBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('[EditCalendar] Click en Ver disponibilidad');
            if (elements.calendarModal) {
                const modal = new bootstrap.Modal(elements.calendarModal);
                modal.show();
            }
        });
    }

    console.log('[EditCalendar] Event listeners agregados');

    /**
     * Inicializar
     */
    console.log('[EditCalendar] Llamando loadUserCalendars y updateCalendarButton');
    loadUserCalendars();
    updateCalendarButton();

    // Si hay horario ya seleccionado, mostrarlo
    const existingStartTime = elements.selectedStartTimeInput?.value;
    if (existingStartTime) {
        const tiempoEstimado = parseFloat(elements.tiempoEstimadoInput?.value) || 1;
        const start = new Date(existingStartTime);
        const end = new Date(start.getTime() + tiempoEstimado * 60 * 60 * 1000);
        displaySelectedTime(start, end);
    }

    console.log('[EditCalendar] ✅ Inicialización completada');
}

// Auto-inicializar cuando el DOM esté listo
function tryInitialize() {
    const form = document.getElementById('formEditarTarea');
    if (form) {
        console.log('[EditCalendar] Formulario encontrado, inicializando...');
        initializeEditTaskCalendar();
    } else {
        console.log('[EditCalendar] Formulario NO encontrado');
    }
}

if (document.readyState === 'loading') {
    console.log('[EditCalendar] DOM está cargando, esperando DOMContentLoaded');
    document.addEventListener('DOMContentLoaded', tryInitialize);
} else {
    console.log('[EditCalendar] DOM ya está listo, inicializando inmediatamente');
    tryInitialize();
}
