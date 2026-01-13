{{-- resources/views/components/calendar-modal.blade.php --}}
{{--
Componente modal reutilizable para mostrar FullCalendar

Props:
- $modalId (string): ID único del modal (ej: 'calendarModal')
- $eventsUrl (string): URL para obtener eventos (ej: '/agenda/events')
- $resourcesUrl (string): URL para obtener recursos (ej: '/agenda/resources')
- $onSelectCallback (string): Nombre de función JavaScript a llamar cuando se selecciona un slot
--}}

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false" 
    data-events-url="{{ $eventsUrl }}"
    data-resources-url="{{ $resourcesUrl }}">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-week me-2"></i>Disponibilidad de colaboradores
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" style="padding: 15px;">
                <div id="{{ $modalId }}-calendar" style="height: 70vh;"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>