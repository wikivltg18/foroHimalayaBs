<x-app-layout>
    <x-slot name="titulo">Editar tarea</x-slot>

    <div class="form-card p-5">
        <form id="formEditarTarea" action="{{ route('tareas.updateInColumn', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id
]) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- mensajes de validación --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <h1 class="fw-bold pb-3" style="color:#003B7B;">Editar tarea</h1>
                    <div class="alert alert-info mb-3">
                        La tarea pertenece a la columna: <strong>{{ $columna->nombre_de_la_columna }}</strong>
                    </div>
                    <div class="section-title h5 fw-bold py-3" style="color:#003B7B;">Información general</div>
                </div>
            </div>

            {{-- Aviso y opción de REACTIVAR SOLO en edición --}}
            @if($tarea->finalizada_at)
                <div class="alert alert-warning">
                    Esta tarea está finalizada desde
                    <strong>{{ dtz($tarea->finalizada_at, 'd/m/Y g:i a') }}</strong>.
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="reactivar" name="reactivar">
                        <label class="form-check-label" for="reactivar">
                            Re-activar la tarea (volver a abrirla)
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">Al re-activarla, se limpiará <code>finalizada_at</code> y
                        <code>finalizada_por</code>.</small>
                </div>
            @endif

            <div class="row mt-3">
                <div class="col-md-10">
                    <div class="fw-bold mb-2" style="color:#003B7B;">Nombre de la tarea:</div>
                    <input type="text" class="form-control" name="titulo" value="{{ old('titulo', $tarea->titulo) }}"
                        required>
                </div>
                <div class="col-md-2">
                    <label class="label mb-2">Estado:</label>
                    <select class="form-select" name="estado_id" required>
                        <option disabled>Seleccione un estado</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}" @selected(old('estado_id', $tarea->estado_id) == $estado->id)>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="section-title fw-bold" style="color:#003B7B;">Asignación</div>

                    <label class="label mb-2">Área asignada:</label>
                    <select class="form-select mb-2" name="area_id" id="area_id" required>
                        <option disabled {{ old('area_id', $tarea->area_id) ? '' : 'selected' }}>
                            @if($areas->isEmpty())
                                No hay áreas contratadas
                            @else
                                Seleccione un área
                            @endif
                        </option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area_id', $tarea->area_id) == $area->id)>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <label class="label mb-2">Colaborador:</label>
                    <select class="form-select mb-2" name="usuario_id" id="usuario_id" required disabled>
                        <option value="" disabled>Seleccione un colaborador</option>
                    </select>

                    @if($areas->isEmpty())
                        <div class="alert alert-warning mt-2">
                            Este servicio no tiene horas contratadas por área. Configure horas en la “configuración de
                            servicios”.
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <div class="label fw-bold" style="color:#003B7B;">Gestión de tiempo</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div>Tiempo disponible:</div>
                            <div id="tiempoDisponible" class="text-success fw-bold" style="font-size:30px;">0</div>
                        </div>

                        <div class="col-md-6">
                            <label class="mb-2">Tiempo estimado (h):</label>
                            <input type="number" min="0" step="0.5" class="form-control" name="tiempo_estimado_h"
                                value="{{ old('tiempo_estimado_h', $tarea->tiempo_estimado_h) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="fw-bold" style="color:#003B7B;">Cronograma</div>

                <div class="col-md-6">
                    <label class="label mb-2">Fecha de creación:</label>
                    <input type="datetime-local" class="form-control" id="fechaCreacion" readonly>
                </div>

                <div class="col-md-6">
                    <label class="label mb-2">Fecha de entrega:</label>
                    <input type="date" class="form-control" name="fecha_de_entrega"
                        value="{{ old('fecha_de_entrega', dtz($tarea->fecha_de_entrega, 'Y-m-d')) }}">
                </div>
            </div>

            {{-- SECCIÓN: Google Calendar --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <div id="googleCalendarSelection" class="mb-3"></div>
                </div>
            </div>

            {{-- SECCIÓN: Ver disponibilidad --}}
            <div class="row mt-3" id="calendarButtonContainer" style="display: none;">
                <div class="col-md-12">
                    <button type="button" class="btn btn-outline-info" id="btnViewCalendar">
                        <i class="bi bi-calendar-check me-2"></i>Ver disponibilidad de colaboradores
                    </button>
                    <small class="text-muted ms-2">Haz clic para seleccionar un horario disponible en el calendario</small>
                </div>
            </div>

            {{-- SECCIÓN: Horario seleccionado --}}
            <div class="row mt-3">
                <div class="col-md-12">
                    <div id="selectedTimeDisplay" style="display: none;" class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Horario seleccionado:</strong>
                        <span id="selectedTimeText"></span>
                    </div>
                </div>
            </div>

            {{-- Campos ocultos para almacenar selecciones del calendario --}}
            <input type="hidden" name="selected_start_time" id="selectedStartTime" 
                value="{{ old('selected_start_time', $primerBloque?->inicio?->toIso8601String()) }}">
            <input type="hidden" name="selected_user_id" id="selectedUserId" 
                value="{{ old('selected_user_id', $primerBloque?->user_id) }}">
            <input type="hidden" name="google_calendar_id" id="selectedGoogleCalendar" 
                value="{{ old('google_calendar_id', $tarea->google_calendar_id ?? 'primary') }}">

            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="label mb-2">Descripción:</label>

                    {{-- Quill --}}
                    <div id="editor-container" style="height: 200px;" data-upload-url="{{ route('quill.upload') }}"
                        data-csrf-token="{{ csrf_token() }}" data-target-hidden-id="descripcion"
                        data-source-hidden-id="descripcion"></div>

                    <input type="hidden" name="descripcion" id="descripcion"
                        value="{{ old('descripcion', $tarea->descripcion) }}">

                    <input type="file" id="quill-image-input" accept="image/*" class="d-none">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('configuracion.servicios.tableros.show', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-outline-secondary btn-pill">Volver</a>

                <button type="submit" class="btn btn-primary btn-pill">Guardar cambios</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Fecha/hora informativa
            function actualizarFechaHora() {
                const input = document.getElementById("fechaCreacion");
                if (!input) return;
                const d = new Date();
                const f = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                const t = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
                input.value = `${f}T${t}`;
            }
            document.addEventListener("DOMContentLoaded", () => {
                actualizarFechaHora();
                setInterval(actualizarFechaHora, 60000);
            });

            // Selects & horas como en create
            const areaSelect = document.getElementById('area_id');
            const usuarioSelect = document.getElementById('usuario_id');
            const tiempoDisponibleEl = document.getElementById('tiempoDisponible');

            function setUsuariosOptions(users, preselectedId = null) {
                usuarioSelect.innerHTML = '';
                const ph = document.createElement('option');
                ph.disabled = true; ph.selected = true;
                ph.textContent = users.length ? 'Seleccione un colaborador' : 'No hay colaboradores';
                ph.value = '';
                usuarioSelect.appendChild(ph);

                users.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    if (preselectedId && String(preselectedId) === String(u.id)) opt.selected = true;
                    usuarioSelect.appendChild(opt);
                });

                usuarioSelect.disabled = users.length === 0;
            }

            async function cargarUsuariosPorArea(areaId, preselectedId = null) {
                if (!areaId) { setUsuariosOptions([]); return; }
                usuarioSelect.disabled = true;
                usuarioSelect.innerHTML = '<option selected disabled>Cargando...</option>';

                try {
                    const base = "{{ route('ajax.areas.usuarios', ':area') }}".replace(':area', areaId);
                    const url = new URL(base, window.location.origin);
                    url.searchParams.set('servicio_id', "{{ $servicio->id }}");

                    const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const users = await res.json();
                    setUsuariosOptions(users, preselectedId);
                } catch (e) {
                    console.error('Error cargando usuarios:', e);
                    setUsuariosOptions([]);
                    alert('No fue posible cargar los colaboradores de esa área.');
                }
            }

            async function cargarTiempoDisponible(areaId) {
                if (!areaId) { tiempoDisponibleEl.textContent = '0'; return; }

                try {
                    const url = "{{ route('ajax.servicios.areas.horas', [':servicio', ':area']) }}"
                        .replace(':servicio', "{{ $servicio->id }}")
                        .replace(':area', areaId);

                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const data = await res.json();
                    tiempoDisponibleEl.textContent = (data.horas ?? 0);
                } catch (e) {
                    console.error('Error cargando horas:', e);
                    tiempoDisponibleEl.textContent = '0';
                }
            }

            async function onAreaChange(areaId, preselectedUserId = null) {
                await Promise.all([
                    cargarUsuariosPorArea(areaId, preselectedUserId),
                    cargarTiempoDisponible(areaId),
                ]);
            }

            areaSelect?.addEventListener('change', (e) => onAreaChange(e.target.value, null));

            document.addEventListener('DOMContentLoaded', () => {
                const currentArea = "{{ old('area_id', $tarea->area_id) }}";
                const currentUserId = "{{ old('usuario_id', $tarea->usuario_id) }}";

                if (currentArea) {
                    onAreaChange(currentArea, currentUserId);
                } else {
                    setUsuariosOptions([]);
                    if (tiempoDisponibleEl) tiempoDisponibleEl.textContent = '0';
                }
            });
        </script>
    @endpush

    {{-- Modal de Calendario --}}
    <x-calendar-modal 
        :modalId="'taskCalendarModal'"
        :eventsUrl="route('agenda.events')"
        :resourcesUrl="route('agenda.resources')"
        :onSelectCallback="'onTaskCalendarSelect'"
    />

    {{-- Cargar script del calendario --}}
    @push('scripts')
        @vite('resources/js/edit-task-calendar.js')
    @endpush
</x-app-layout>