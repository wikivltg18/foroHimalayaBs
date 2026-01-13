import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('calendar');
    if (!calEl) return;

    // Timezone
    const timeZone = document.querySelector('meta[name="app-timezone"]')?.content || 'UTC';

    const calendar = new Calendar(calEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: [
            interactionPlugin,
            dayGridPlugin,
            timeGridPlugin,
            listPlugin,
            resourceTimelinePlugin
        ],
        timeZone: timeZone,
        initialView: 'resourceTimelineWeek',
        height: 'auto',
        nowIndicator: true,

        // Read-only settings
        editable: false,
        droppable: false,
        selectable: false,

        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        slotDuration: '00:15:00',
        resourceAreaHeaderContent: 'Colaborador',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimelineDay,resourceTimelineWeek,dayGridMonth,listWeek'
        },
        businessHours: [
            { daysOfWeek: [1, 2, 3, 4, 5], startTime: '08:00', endTime: '12:15' },
            { daysOfWeek: [1, 2, 3, 4, 5], startTime: '13:45', endTime: '18:00' },
        ],
        resources: {
            url: '/ajax/google/resources',
            method: 'GET'
        },
        events: function (info, success, failure) {
            fetch(`/ajax/google/events?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`)
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(events => {
                    // console.log('Events fetched:', events);
                    success(events);
                })
                .catch(err => { console.error('Error events:', err); failure(err); });
        },
        loading: function (isLoading) {
            // Optional spinner logic
        }
    });

    calendar.render();
});
