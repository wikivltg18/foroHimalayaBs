// resources/js/calendar-modal.js
// Inicialización del modal de calendario con FullCalendar desde npm

import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';

export function initCalendarModal(modalId, eventsUrl, resourcesUrl, onSelectCallback) {
    const calendarId = `${modalId}-calendar`;
    let calendarInstance = null;

    // Mostrar modal: inicializar FullCalendar
    const modalEl = document.getElementById(modalId);
    if (!modalEl) {
        console.warn(`[CalendarModal] Modal ${modalId} not found`);
        return;
    }

    modalEl.addEventListener('show.bs.modal', function () {
        if (calendarInstance) {
            calendarInstance.destroy();
        }

        const calEl = document.getElementById(calendarId);
        if (!calEl) {
            console.warn(`[CalendarModal] Calendar element ${calendarId} not found`);
            return;
        }

        console.log(`[CalendarModal] Initializing calendar for ${modalId}`);

        calendarInstance = new Calendar(calEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            plugins: [
                interactionPlugin,
                dayGridPlugin,
                timeGridPlugin,
                listPlugin,
                resourceTimelinePlugin
            ],

            initialView: 'resourceTimelineWeek',
            height: 'auto',
            nowIndicator: true,
            editable: false,
            selectable: true,
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            slotDuration: '00:15:00',
            resourceAreaHeaderContent: 'Colaborador',

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'resourceTimelineDay,resourceTimelineWeek,dayGridMonth'
            },

            businessHours: [
                { daysOfWeek: [1, 2, 3, 4, 5], startTime: '08:00', endTime: '12:15' },
                { daysOfWeek: [1, 2, 3, 4, 5], startTime: '13:45', endTime: '18:00' }
            ],

            resources: {
                url: resourcesUrl,
                method: 'GET'
            },

            events: function (info, success, failure) {
                fetch(`${eventsUrl}?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`)
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(success)
                    .catch(err => {
                        console.error('[CalendarModal] Error cargando eventos:', err);
                        failure(err);
                    });
            },

            // Seleccionar un espacio: capturar horario
            select: function (sel) {
                if (window[onSelectCallback] && typeof window[onSelectCallback] === 'function') {
                    window[onSelectCallback]({
                        resourceId: sel.resource?.id,
                        startStr: sel.startStr,
                        endStr: sel.endStr,
                        modalId: modalId
                    });
                }
            }
        });

        calendarInstance.render();
        console.log(`[CalendarModal] Calendar rendered for ${modalId}`);
    });

    // Limpiar al cerrar
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (calendarInstance) {
            calendarInstance.destroy();
            calendarInstance = null;
            console.log(`[CalendarModal] Calendar destroyed for ${modalId}`);
        }
    });

    console.log(`[CalendarModal] Event listeners attached for ${modalId}`);
}
