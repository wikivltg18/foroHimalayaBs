// resources/js/create-task-calendar.js
// Manejo de calendario y selección de horarios en crear tarea

import * as bootstrap from 'bootstrap';
import { initCalendarModal } from './calendar-modal.js';

// Inicializar modal de calendario
document.addEventListener('DOMContentLoaded', function () {
    const eventsUrl = document.querySelector('[data-events-url]')?.dataset.eventsUrl || '/agenda/events';
    const resourcesUrl = document.querySelector('[data-resources-url]')?.dataset.resourcesUrl || '/agenda/resources';

    initCalendarModal('taskCalendarModal', eventsUrl, resourcesUrl, 'onTaskCalendarSelect');
});

export function initializeTaskCalendar() {
    console.log('[Calendar] Inicializando calendario en tarea');

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

    console.log('[Calendar] Elementos encontrados:', {
        usuarioSelect: !!elements.usuarioSelect,
        tiempoEstimadoInput: !!elements.tiempoEstimadoInput,
        calendarButtonContainer: !!elements.calendarButtonContainer,
        calendarModal: !!elements.calendarModal,
    });

    // Verificar que existan los elementos básicos
    if (!elements.usuarioSelect || !elements.tiempoEstimadoInput) {
        console.warn('[Calendar] Elementos críticos no encontrados');
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

        // Intentar auto-seleccionar calendario basándose en email del colaborador
        const usuarioSelect = document.getElementById('usuario_id');
        let selectedCalendar = data.default_calendar || 'primary';

        if (usuarioSelect && usuarioSelect.value) {
            // Obtener email del colaborador seleccionado
            const selectedOption = usuarioSelect.options[usuarioSelect.selectedIndex];
            const userEmail = selectedOption?.dataset?.email;

            if (userEmail) {
                // Buscar calendario que coincida con el email
                const matchingCalendar = data.calendars.find(cal =>
                    cal.id.toLowerCase() === userEmail.toLowerCase() ||
                    cal.summary.toLowerCase().includes(userEmail.toLowerCase())
                );

                if (matchingCalendar) {
                    selectedCalendar = matchingCalendar.id;
                    console.log('[Calendar] Auto-seleccionado calendario:', matchingCalendar.summary, 'para usuario:', userEmail);
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

        // Establecer valores iniciales
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

        console.log('[Calendar] updateCalendarButton:', { usuarioId, tiempoEstimado, shouldShow });

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

    // === LÓGICA DE BLOQUES DE TRABAJO (FRONTEND) ===
    const workBlocks = [
        { start: "08:00", end: "12:15" },
        { start: "13:45", end: "18:00" }
    ];

    function addMinutes(date, minutes) {
        return new Date(date.getTime() + minutes * 60000);
    }

    function getNextWorkDay(date) {
        let next = new Date(date);
        next.setDate(next.getDate() + 1);
        if (next.getDay() === 6) next.setDate(next.getDate() + 2); // Sábado -> Lunes
        if (next.getDay() === 0) next.setDate(next.getDate() + 1); // Domingo -> Lunes
        if (date.getDay() === 5) next.setDate(next.getDate() + 3); // Viernes -> Lunes (dependiendo de la hora, pero lógica simplificada: siguiente día laboral)
        return next;
    }

    /**
     * Genera eventos divididos según bloques de trabajo
     */
    function createSplitEvents(startDate, durationMinutes) {
        let events = [];
        let remaining = durationMinutes;
        let currentDate = new Date(startDate);
        // Ajuste inicial: si es fin de semana, saltar al lunes
        if (currentDate.getDay() === 6) currentDate.setDate(currentDate.getDate() + 2);
        if (currentDate.getDay() === 0) currentDate.setDate(currentDate.getDate() + 1);

        let safetyCounter = 0;
        while (remaining > 0 && safetyCounter < 365) {
            for (let block of workBlocks) {
                if (remaining <= 0) break;

                let blockStart = new Date(currentDate);
                let [hStart, mStart] = block.start.split(":").map(Number);
                blockStart.setHours(hStart, mStart, 0, 0);

                let blockEnd = new Date(currentDate);
                let [hEnd, mEnd] = block.end.split(":").map(Number);
                blockEnd.setHours(hEnd, mEnd, 0, 0);

                // Si la fecha actual ya pasó el fin de este bloque en este día, saltar
                if (currentDate > blockEnd) continue;

                // Definir inicio efectivo (si la hora seleccionada está dentro del bloque)
                let effectiveStart = (currentDate > blockStart) ? new Date(currentDate) : blockStart;

                let blockDuration = (blockEnd - effectiveStart) / 60000; // minutos disponibles

                if (blockDuration <= 0) continue;

                if (remaining <= blockDuration) {
                    events.push({
                        start: effectiveStart,
                        end: addMinutes(effectiveStart, remaining)
                    });
                    remaining = 0;
                } else {
                    events.push({
                        start: effectiveStart,
                        end: blockEnd
                    });
                    remaining -= blockDuration;
                }
            }

            if (remaining > 0) {
                currentDate = getNextWorkDay(currentDate);
                // Reiniciar hora al inicio del primer bloque para el nuevo día
                let [hFirst, mFirst] = workBlocks[0].start.split(":").map(Number);
                currentDate.setHours(hFirst, mFirst, 0, 0);
            }
            safetyCounter++;
        }
        return events;
    }

    /**
     * Callback cuando se selecciona un slot en el calendario
     */
    window.onTaskCalendarSelect = function (data) {
        const tiempoEstimadoH = parseFloat(elements.tiempoEstimadoInput.value) || 1;
        const tiempoEstimadoMin = tiempoEstimadoH * 60;
        const start = new Date(data.startStr); // Fecha seleccionada por el usuario

        // Generar proyección de bloques
        const projectedBlocks = createSplitEvents(start, tiempoEstimadoMin);

        if (projectedBlocks.length === 0) {
            showNotification('❌ No se pudo programar en este horario.', 'danger');
            return;
        }

        const realStart = projectedBlocks[0].start;
        const realEnd = projectedBlocks[projectedBlocks.length - 1].end;

        // Guardar selección
        if (elements.selectedStartTimeInput) {
            elements.selectedStartTimeInput.value = start.toISOString();
        }
        if (elements.selectedUserIdInput) {
            elements.selectedUserIdInput.value = data.resourceId;
        }

        // Mostrar confirmación visual
        displaySelectedTime(realStart, realEnd);

        let msg = `✅ Horario: ${realStart.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${realEnd.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        if (projectedBlocks.length > 1) {
            msg += `<br> <small>(Dividido en ${projectedBlocks.length} bloques por pausas/días)</small>`;
        }

        showNotification(msg, 'success');

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
            console.log('[Calendar] Usuario seleccionado:', e.target.value);
            updateCalendarButton();
            // Recargar calendarios para auto-seleccionar el del colaborador
            loadUserCalendars();
        });
    }

    if (elements.tiempoEstimadoInput) {
        elements.tiempoEstimadoInput.addEventListener('input', (e) => {
            console.log('[Calendar] Tiempo estimado:', e.target.value);
            updateCalendarButton();
        });
    }

    const viewCalendarBtn = document.getElementById('btnViewCalendar');
    if (viewCalendarBtn) {
        viewCalendarBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('[Calendar] Click en Ver disponibilidad');
            if (elements.calendarModal) {
                const modal = new bootstrap.Modal(elements.calendarModal);
                modal.show();
            }
        });
    }

    console.log('[Calendar] Event listeners agregados');

    /**
     * Inicializar
     */
    console.log('[Calendar] Llamando loadUserCalendars y updateCalendarButton');
    loadUserCalendars();
    updateCalendarButton();

    console.log('[Calendar] ✅ Inicialización completada');
}

// Auto-inicializar cuando el DOM esté listo
function tryInitialize() {
    const form = document.getElementById('formCrearTarea');
    if (form) {
        console.log('[Calendar] Formulario encontrado, inicializando...');
        initializeTaskCalendar();
    } else {
        console.log('[Calendar] Formulario NO encontrado');
    }
}

if (document.readyState === 'loading') {
    console.log('[Calendar] DOM está cargando, esperando DOMContentLoaded');
    document.addEventListener('DOMContentLoaded', tryInitialize);
} else {
    console.log('[Calendar] DOM ya está listo, inicializando inmediatamente');
    tryInitialize();
}
