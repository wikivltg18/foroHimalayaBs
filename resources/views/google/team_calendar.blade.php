<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('google.calendars') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Volver a Configuración
        </a>
    </x-slot>

    <x-slot name="titulo">
        Calendario de Equipo (Solo Lectura)
    </x-slot>

    <x-slot name="slot">
        <div class="container-fluid py-4">
            <div class="alert alert-light mb-3">
                <i class="bi bi-info-circle me-2"></i>
                Esta vista es de solo lectura. Para programar tareas, usa la vista de edición de cada tarea o la Agenda completa.
            </div>

            <div id="calendar"></div>
        </div>

        @push('styles')
            <!-- FullCalendar Scheduler -->
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.10/index.global.min.js"></script>
            <style>
                #calendar {
                    min-height: 70vh;
                }
            </style>
        @endpush
    </x-slot>

    @push('scripts')
        @vite('resources/js/team_calendar.js')
    @endpush
</x-app-layout>
