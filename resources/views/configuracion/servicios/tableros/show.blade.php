{{-- resources/views/configuracion/servicios/tableros/show.blade.php --}}
<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('configuracion.servicios.tableros.index', ['cliente' => $cliente->id]) }}"
            class="btn btn-secondary me-2">
            Volver a tableros
        </a>
        {{-- <a href="{{ route('configuracion.servicios.tableros.edit', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-primary">
            Editar tablero
        </a> --}}
    </x-slot>

    <x-slot name="titulo">
        {{ $tablero->nombre_del_tablero }}
    </x-slot>

    <x-slot name="slot">
        @php
            /**
             * Retorna la clase de badge Bootstrap según el nombre del estado.
             */
            $estadoBadgeClass = function (?string $estadoNombre): string {
                $n = mb_strtolower(trim($estadoNombre ?? ''));

                return match ($n) {
                    'programada', 'pendiente' => 'bg-info',
                    'en progreso', 'wip' => 'bg-warning',
                    'finalizada', 'completada' => 'bg-success',
                    'bloqueada' => 'bg-danger',
                    default => 'bg-secondary',
                };
            };
        @endphp

        <div class="card rounded shadow border-0 mb-3">
            <div class="p-3 rounded">
                <div class="row">
                    <div class="col-md-8">
                        <p class="fw-bold m-0">
                            Servicio:
                            {{ $tablero->nombre_del_servicio ?? ($servicio->nombre_servicio ?? $servicio->nombre_del_servicio) }}
                        </p>
                        <p class="m-0">
                            Tipo:
                            {{ $tablero->nombre_tipo_de_servicio ?? optional($servicio->tipo)->nombre ?? '—' }}
                            · Modalidad:
                            {{ $tablero->nombre_modalidad ?? optional($servicio->modalidad)->nombre ?? '—' }}
                        </p>
                        <p class="text-muted m-0">
                            Cliente: {{ $tablero->nombre_cliente ?? $cliente->nombre }}
                        </p>
                        @php
                            $estadoTableroNombre = $tablero->estado->nombre ?? '—';
                        @endphp
                        <p class="text-muted m-0">
                            Creado: {{ optional($tablero->created_at)?->format('d/m/Y H:i') }}
                            · Estado:
                            <span class="badge {{ $estadoBadgeClass($estadoTableroNombre) }}">
                                {{ $estadoTableroNombre }}
                            </span>
                        </p>

                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                        {{--<a href="{{ route('configuracion.servicios.tableros.edit', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-outline-primary">
                            Editar
                        </a>--}}
                    </div>
                </div>
            </div>

            {{-- Lienzo Kanban: columnas horizontales --}}
            <div class="card-body">
                <div class="d-flex flex-row gap-3 overflow-auto pb-2">
                    @forelse($tablero->columnas as $col)
                                        <div class="card flex-shrink-0 border-0 shadow-sm" style="min-width: 320px; max-width: 320px;">
                                            <div class="card-header text-white fw-bold text-center rounded p-1"
                                                style="background-color:#003B7B;">
                                                <div class="small text-white-50">
                                                    Fase: {{ $col->posicion ?? $col->orden }}
                                                </div>
                                                <h5 class="card-title text-white fw-bold text-center">
                                                    {{ $col->nombre_de_la_columna }}
                                                </h5>
                                            </div>

                                            {{-- Lista de tareas --}}
                                            <div class="card-body" style="background:#F6F8FB;">
                                                @forelse($col->tareas as $tarea)
                                                                        <div class="card mb-2 shadow-sm border-0 task-card">
                                                                            <a href="{{ route('tareas.show', [
                                                        'cliente' => $cliente->id,
                                                        'servicio' => $servicio->id,
                                                        'tablero' => $tablero->id,
                                                        'columna' => $col->id,
                                                        'tarea' => $tarea->id,
                                                    ]) }}" class="text-decoration-none">
                                                                                <div class="card-body p-3">
                                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                                        <div>
                                                                                            <div class="fw-bold" style="color:#003B7B;">
                                                                                                {{ $tarea->titulo }}
                                                                                            </div>
                                                                                            <div class="small text-muted">
                                                                                                Área: {{ optional($tarea->area)->nombre ?? '—' }}
                                                                                            </div>
                                                                                        </div>
                                                                                        @php
                                                                                            $estadoTareaNombre = optional($tarea->estado)->nombre ?? '—';
                                                                                        @endphp
                                                                                        <span class="badge {{ $estadoBadgeClass($estadoTareaNombre) }}">
                                                                                            {{ $estadoTareaNombre }}
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                @empty
                                                    <div class="text-center text-muted small">
                                                        No hay tareas en esta columna.
                                                    </div>
                                                @endforelse

                                                {{-- Botón crear tarea en esta columna --}}
                                                <a id="addTask-{{ $col->id }}" data-col-name="{{ $col->nombre_de_la_columna }}"
                                                    data-col-pos="{{ $col->posicion ?? $col->orden }}"
                                                    class="btn btn-sm btn-outline-primary w-100 mt-2" href="{{ route('tareas.createInColumn', [
                            'cliente' => $cliente->id,
                            'servicio' => $servicio->id,
                            'tablero' => $tablero->id,
                            'columna' => $col->id,
                        ]) }}">
                                                    + Añade una tarea
                                                </a>
                                            </div>
                                        </div>
                    @empty
                        <div class="text-muted">Este tablero no tiene columnas definidas.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </x-slot>
    @push('scripts')
        <script>
            (function () {
                function fireCreatedSuccess(message) {
                    Swal.fire({
                        title: '¡Tablero creado!',
                        text: message || 'Se creó correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                }

                function handleSessionAlerts() {
                    // 1) Flash de Laravel: success / status / message
                    @php
                        $flashMsg = session('success') ?? session('status') ?? session('message');
                    @endphp
                    @if($flashMsg)
                        fireCreatedSuccess({!! json_encode($flashMsg) !!});
                        return true;
                    @else
                        return false;
                    @endif
                                    }

                function handleQueryParam() {
                    // 2) Param opcional ?created=1
                    const p = new URLSearchParams(location.search);
                    if (p.get('created') === '1') {
                        // Mensaje por defecto o usa p.get('msg') si quieres pasar un texto por URL
                        fireCreatedSuccess(p.get('msg'));
                        return true;
                    }
                    return false;
                }

                function init() {
                    // Dispara en este orden: flash -> query param
                    if (handleSessionAlerts()) return;
                    handleQueryParam();
                }

                ['DOMContentLoaded', 'turbo:load', 'livewire:load'].forEach(evt =>
                    document.addEventListener(evt, init, { once: true })
                );
                if (document.readyState === 'interactive' || document.readyState === 'complete') init();
            })();
        </script>
    @endpush



</x-app-layout>