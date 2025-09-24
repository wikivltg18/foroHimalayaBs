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
             * Retorna la clase de badge Bootstrap 5.3+ según el nombre del estado.
             * Usa 'text-bg-*' para colorear el fondo del badge.
             * Incluye soporte para estados de tablero (Activo/Terminado) y de tareas.
             */
            $estadoBadgeClass = function (?string $estadoNombre): string {
                $n = mb_strtolower(trim($estadoNombre ?? ''));

                return match ($n) {
                    // Tablero (ej.: Activo / Terminado)
                    'activo' => 'text-bg-success',
                    'terminado' => 'text-bg-secondary',

                    // Tareas / workflow genérico
                    'programada', 'pendiente' => 'text-bg-info p-2 text-white',
                    'en progreso', 'wip' => 'text-bg-warning p-2 text-white',
                    'finalizada', 'completada' => 'text-bg-success p-2',
                    'bloqueada' => 'text-bg-danger',

                    default => 'text-bg-secondary',
                };
            };

            /**
             * Clase de borde Bootstrap según estado de la TAREA.
             * Devuelve: 'border-info' | 'border-warning' | 'border-success' | 'border-danger' | 'border-secondary'
             */
            $estadoBorderClass = function (?string $estadoNombre): string {
                $n = mb_strtolower(trim($estadoNombre ?? ''));
                return match ($n) {
                    'programada', 'pendiente' => 'border-info',
                    'en progreso', 'wip' => 'border-warning',
                    'finalizada', 'completada' => 'border-success',
                    'bloqueada' => 'border-danger',
                    default => 'border-secondary',
                };
            };

            // ========= NUEVO: conteos sin scopes y flag de tablero =========
            $finalIds = \App\Models\EstadoTarea::finalIds();

            $total = $tablero->tareas_count ?? $tablero->tareas()->count();
            $pend  = $tablero->pendientes_count
                    ?? $tablero->tareas()->where(function ($q) use ($finalIds) {
                            $q->whereNull('estado_id')->orWhereNotIn('estado_id', $finalIds);
                       })->count();
            $comp  = $tablero->completas_count
                    ?? $tablero->tareas()->whereIn('estado_id', $finalIds)->count();

            $canFinalize = $pend === 0 && $total > 0; // (opcional) exige >= 1 tarea total
            $tableroTerminado = $tablero->isTerminated();
            // ===============================================================
        @endphp

        <div class="card rounded shadow border-0 mb-3">
            <h5 class="service-title fw-bold m-2">Finalización del tablero</h5>
            <div class="d-flex flex-wrap gap-2 m-2">
                {{-- Finalizar tablero --}}
                <form method="POST" action="{{ route('tableros.estado.update', $tablero) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="estado" value="Terminado">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill" {{ $canFinalize ? '' : 'disabled' }}
                        data-bs-toggle="tooltip"
                        title="{{ $canFinalize ? 'Finalizar tablero' : 'No puedes finalizar: hay tareas pendientes' }}">
                        Finalizar tablero
                    </button>
                </form>

                {{-- Reactivar tablero --}}
                <form method="POST" action="{{ route('tableros.estado.update', $tablero) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="estado" value="Activo">
                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill">
                        Marcar como Activo
                    </button>
                </form>
            </div>

            <div class="small text-muted m-2">
                Tareas: {{ $total }} · Pendientes: {{ $pend }} · Completas: {{ $comp }}
            </div>
        </div>

        <div class="card rounded shadow border-0 mb-3">
            <div class="p-3 rounded">
                <div class="row">
                    <div
                        class="service-header d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-1 gap-md-2 mb-3">
                        {{-- Lado izquierdo: título + subtítulo --}}
                        <div class="flex-grow-1">
                            <h5 class="service-title fw-bold mb-1">
                                {{ $tablero->nombre_del_servicio ?? ($servicio->nombre_servicio ?? $servicio->nombre_del_servicio) }}
                            </h5>

                            <p class="service-subtle mb-0">
                                <span class="fw-bold">Tipo:</span>
                                {{ $tablero->nombre_tipo_de_servicio ?? optional($servicio->tipo)->nombre ?? '—' }}
                                - <span class="fw-bold">Modalidad:</span>
                                {{ $tablero->nombre_modalidad ?? optional($servicio->modalidad)->nombre ?? '—' }}
                            </p>
                        </div>

                        {{-- Lado derecho: cliente, fecha y estado --}}
                        @php
                            $estadoTableroNombre = $tablero->estado->nombre ?? '—';
                        @endphp
                        <div class="d-flex flex-row">
                            <div>
                                <p class="service-subtle mb-0">
                                    <span class="fw-bold">Cliente:</span>
                                    {{ $tablero->nombre_cliente ?? $cliente->nombre }}
                                </p>
                                <p class="service-subtle mb-0 d-flex d-md-block align-items-center gap-2">
                                    <span>
                                        <span class="fw-bold">Creado:</span>
                                        {{ optional($tablero->created_at)?->format('d/m/Y H:i') }}
                                    </span>
                                </p>
                            </div>
                            <div class="px-3 d-flex justify-content-center align-items-center">
                                <span class="badge rounded-pill p-2 {{ $estadoBadgeClass($estadoTableroNombre) }}">
                                    {{ $estadoTableroNombre }}
                                </span>
                            </div>
                        </div>
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
                            <div class="card-header text-white fw-bold text-center rounded p-2
                                                                            d-flex flex-column justify-content-center align-items-center gap-1"
                                style="background-color:#003B7B; min-height:82px;">
                                <div class="small text-white-50">
                                    Fase: {{ $col->posicion ?? $col->orden }}
                                </div>
                                <h5 class="card-title text-white fw-bold mb-0">
                                    {{ $col->nombre_de_la_columna }}
                                </h5>
                            </div>
                            {{-- Lista de tareas --}}
                            <div class="card-body {{ $col->tareas->isEmpty() ? 'd-flex flex-column justify-content-end align-items-center text-center' : '' }}"
                                style="background:#F6F8FB; ">
                                @forelse($col->tareas as $tarea)
                                    @php $estadoTareaNombre = optional($tarea->estado)->nombre ?? '—'; @endphp
                                    <div
                                        class="card mb-2 shadow-sm task-card border-0 border-start border-4 {{ $estadoBorderClass($estadoTareaNombre) }}">

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
                                                    <span
                                                        class="badge rounded-pill {{ $estadoBadgeClass($estadoTareaNombre) }}">
                                                        {{ $estadoTareaNombre }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    {{-- Estado vacío centrado --}}
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor"
                                            class="bi bi-inbox-fill mb-2 text-muted" viewBox="0 0 16 16" aria-hidden="true">
                                            <path
                                                d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm-1.17-.437A1.5 1.5 0 0 1 4.98 3h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a.5.5 0 0 1 .106.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374z" />
                                        </svg>

                                        <div class="text-muted small mb-3">
                                            No hay tareas en esta columna.
                                        </div>

                                        {{-- Botón crear tarea centrado (deshabilitado si el tablero está Terminado) --}}
                                        @if(!$tableroTerminado)
                                            <a id="addTask-{{ $col->id }}"
                                               data-col-name="{{ $col->nombre_de_la_columna }}"
                                               data-col-pos="{{ $col->posicion ?? $col->orden }}"
                                               class="btn btn-sm btn-outline-primary w-100 mt-2"
                                               href="{{ route('tareas.createInColumn', [
                                                    'cliente' => $cliente->id,
                                                    'servicio' => $servicio->id,
                                                    'tablero' => $tablero->id,
                                                    'columna' => $col->id,
                                               ]) }}">
                                                + Añade una tarea
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary w-100 mt-2" disabled
                                                    title="El tablero está terminado">
                                                + Añade una tarea
                                            </button>
                                        @endif

                                    </div>
                                @endforelse

                                {{-- Botón abajo cuando SÍ hay tareas (respetando bloqueo por tablero Terminado) --}}
                                @if($col->tareas->isNotEmpty())
                                    @if(!$tableroTerminado)
                                        <a id="addTask-{{ $col->id }}"
                                            data-col-name="{{ $col->nombre_de_la_columna }}"
                                            data-col-pos="{{ $col->posicion ?? $col->orden }}"
                                            class="btn btn-sm btn-outline-primary w-100 mt-2"
                                            href="{{ route('tareas.createInColumn', [
                                                'cliente' => $cliente->id,
                                                'servicio' => $servicio->id,
                                                'tablero' => $tablero->id,
                                                'columna' => $col->id,
                                            ]) }}">
                                            + Añade una tarea
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary w-100 mt-2" disabled
                                                title="El tablero está terminado">
                                            + Añade una tarea
                                        </button>
                                    @endif
                                @endif
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
                        fireCreatedSuccess(p.get('msg'));
                        return true;
                    }
                    return false;
                }

                function init() {
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
