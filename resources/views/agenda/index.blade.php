<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('google.calendars') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-google"></i> Configurar Google Calendar
        </a>
    </x-slot>

    <x-slot name="titulo">
        Agenda de colaboradores
    </x-slot>

    <x-slot name="slot">
        <div class="container-fluid py-4">
            <!-- Botón para abrir modal de agendamiento -->
            <div class="mb-3">
                <button type="button" class="btn btn-primary" id="btnOpenScheduleModal">
                    <i class="bi bi-calendar-plus me-2"></i>Programar tarea
                </button>
                <small class="text-muted ms-3">
                    <i class="bi bi-info-circle"></i>
                    También puedes hacer clic en un espacio del calendario para agendar
                </small>
            </div>

            <div id="calendar"></div>
        </div>

        <!-- Modal para seleccionar tarea -->
        <div class="modal fade" id="scheduleTaskModal" tabindex="-1" aria-labelledby="scheduleTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="scheduleTaskModalLabel">
                            <i class="bi bi-calendar-check me-2"></i>Programar tarea
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Búsqueda de tareas -->
                        <div class="mb-3">
                            <label for="taskSearch" class="form-label">Buscar tarea</label>
                            <input type="text" class="form-control" id="taskSearch" placeholder="Escribe para buscar...">
                        </div>

                        <!-- Lista de tareas disponibles -->
                        <div id="taskListContainer" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="text-muted mt-2">Cargando tareas...</p>
                            </div>
                        </div>

                        <input type="hidden" id="selectedTaskId">
                        <input type="hidden" id="selectedUserId">
                        <input type="hidden" id="selectedStartTime">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnScheduleTask" disabled>
                            <i class="bi bi-check-lg me-2"></i>Programar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @push('styles')
            {{-- CSS de FullCalendar Core + Resource Timeline (Scheduler) --}}
        @push('styles')
            <!-- FullCalendar Scheduler (includes Core, Timeline, Resource) -->
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.10/index.global.min.js"></script>
        @endpush

            <style>
                #calendar {
                    min-height: 70vh;
                }
                .task-card {
                    cursor: pointer;
                    transition: all 0.2s;
                    border-left: 4px solid #0d6efd;
                }
                .task-card:hover {
                    background-color: #f8f9fa;
                    transform: translateX(5px);
                }
                .task-card.selected {
                    background-color: #e7f1ff;
                    border-left-color: #0d6efd;
                }
                .task-badge {
                    font-size: 0.75rem;
                }
            </style>
        @endpush
    </x-slot>

    @push('scripts')
        {{-- Load the module via Vite --}}
        @vite('resources/js/agenda.js')
    @endpush
</x-app-layout>
