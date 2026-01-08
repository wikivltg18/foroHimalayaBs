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

    // Obtener configuración desde data-attributes o variables globales si es necesario
    // Para simplificar, hardcodeamos o leemos de meta tags si existen
    const timeZone = document.querySelector('meta[name="app-timezone"]')?.content || 'UTC';

    let availableTasks = [];
    const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleTaskModal'));

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
        editable: true,
        droppable: true,
        selectable: true,
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
            url: '/agenda/resources',
            method: 'GET'
        },
        events: function (info, success, failure) {
            fetch(`/agenda/events?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`)
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(success)
                .catch(err => { console.error('Error events:', err); failure(err); });
        },
        eventDrop: async function (info) {
            await handleEventMove(info);
        },
        eventResize: function (info) {
            info.revert();
        },
        select: function (sel) {
            openScheduleModal(sel.resource?.id, sel.startStr);
        },
        loading: function (isLoading) {
            // Aquí podrías mostrar un spinner global si quisieras
        }
    });

    calendar.render();

    // Abrir modal programáticamente
    const btnOpen = document.getElementById('btnOpenScheduleModal');
    if (btnOpen) {
        btnOpen.addEventListener('click', () => {
            openScheduleModal(null, null);
        });
    }

    // Cargar tareas cuando se abre el modal
    const modalEl = document.getElementById('scheduleTaskModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', () => {
            loadAvailableTasks();
        });
    }

    // Búsqueda de tareas
    let searchTimeout;
    const taskSearch = document.getElementById('taskSearch');
    if (taskSearch) {
        taskSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadAvailableTasks(e.target.value);
            }, 300);
        });
    }

    // Programar tarea
    const btnSchedule = document.getElementById('btnScheduleTask');
    if (btnSchedule) {
        btnSchedule.addEventListener('click', async () => {
            await scheduleTask();
        });
    }

    // Funciones auxiliares
    function openScheduleModal(userId, startTime) {
        document.getElementById('selectedUserId').value = userId || '';
        document.getElementById('selectedStartTime').value = startTime || '';
        document.getElementById('selectedTaskId').value = '';
        if (btnSchedule) btnSchedule.disabled = true;
        scheduleModal.show();
    }

    async function loadAvailableTasks(search = '') {
        const container = document.getElementById('taskListContainer');
        if (!container) return;
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';

        try {
            const url = new URL('/agenda/available-tasks', window.location.origin);
            if (search) url.searchParams.append('search', search);

            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al cargar tareas');

            availableTasks = await response.json();
            renderTaskList(availableTasks);
        } catch (error) {
            container.innerHTML = '<div class="alert alert-danger">Error al cargar las tareas</div>';
            console.error(error);
        }
    }

    function renderTaskList(tasks) {
        const container = document.getElementById('taskListContainer');
        if (!container) return;

        if (tasks.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No se encontraron tareas disponibles</div>';
            return;
        }

        container.innerHTML = tasks.map(task => `
            <div class="card task-card mb-2" data-task-id="${task.id}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${task.titulo}</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                ${task.cliente ? `<span class="badge bg-primary task-badge">${task.cliente}</span>` : ''}
                                ${task.area ? `<span class="badge bg-info task-badge">${task.area}</span>` : ''}
                                ${task.usuario ? `<span class="badge bg-secondary task-badge">${task.usuario}</span>` : ''}
                                ${task.programada ? '<span class="badge bg-warning task-badge">Ya programada</span>' : ''}
                            </div>
                        </div>
                        <div class="text-end">
                            ${task.tiempo_estimado_h ? `<small class="text-muted"><i class="bi bi-clock"></i> ${task.tiempo_estimado_h}h</small>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // Event listeners para seleccionar tarea
        container.querySelectorAll('.task-card').forEach(card => {
            card.addEventListener('click', function () {
                container.querySelectorAll('.task-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedTaskId').value = this.dataset.taskId;
                if (btnSchedule) btnSchedule.disabled = false;
            });
        });
    }

    async function scheduleTask() {
        const taskId = document.getElementById('selectedTaskId').value;
        const userId = document.getElementById('selectedUserId').value;
        const startTime = document.getElementById('selectedStartTime').value;

        if (!taskId) {
            showAlert({ title: 'Error', text: 'Selecciona una tarea', icon: 'error' });
            return;
        }

        if (!userId) {
            showAlert({ title: 'Colaborador requerido', text: 'Selecciona un colaborador del calendario', icon: 'warning' });
            return;
        }

        const btn = document.getElementById('btnScheduleTask');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Programando...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/agenda/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tarea_id: taskId,
                    user_id: userId,
                    start: startTime || new Date().toISOString()
                })
            });

            if (!res.ok) {
                let data;
                try { data = await res.json(); } catch (e) { data = { message: res.statusText }; }
                throw new Error(data?.message || 'Error al programar');
            }

            scheduleModal.hide();
            showAlert({ title: 'Éxito', text: 'Tarea programada correctamente', icon: 'success' });
            calendar.refetchEvents();
        } catch (error) {
            showAlert({ title: 'Error', text: error.message, icon: 'error' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Programar';
        }
    }

    async function handleEventMove(info) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/agenda/move-block', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    block_id: info.event.id,
                    new_start: info.event.start.toISOString()
                })
            });

            if (!res.ok) {
                let data;
                try { data = await res.json(); } catch (e) { data = { message: res.statusText }; }
                const msg = data?.message || 'Error al mover bloque';
                showAlert({ title: 'Error', text: msg, icon: 'error' });
                info.revert();
                return;
            }

            calendar.refetchEvents();
            showAlert({ title: 'Éxito', text: 'Bloque movido correctamente', icon: 'success', timer: 2000 });
        } catch (e) {
            console.error(e);
            showAlert({ title: 'Error', text: e.message || 'Error al comunicarse con el servidor', icon: 'error' });
            info.revert();
        }
    }

    function showAlert(opts) {
        if (window.Swal && typeof Swal.fire === 'function') {
            return Swal.fire(opts);
        } else {
            alert((opts.title ? opts.title + ': ' : '') + (opts.text || opts.html || ''));
            return Promise.resolve();
        }
    }
});
