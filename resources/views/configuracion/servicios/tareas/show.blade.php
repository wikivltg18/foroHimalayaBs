<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('configuracion.servicios.tableros.show', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-secondary me-2">
            Volver al tablero
        </a>
        {{-- (Opcional) botón de editar tarea si lo implementas --}}
        {{-- <a href="{{ route('tareas.edit', [...]) }}" class="btn btn-primary">Editar tarea</a> --}}
    </x-slot>

    <x-slot name="titulo">
        {{ $tarea->titulo }}
    </x-slot>

    <x-slot name="slot">
        @php
            $horasReales = $tarea->timeLogs->sum('duracion_h');
            $creado = optional($tarea->created_at)?->format('d/m/Y g:i a');
            $entrega = optional($tarea->fecha_de_entrega)?->format('d/m/Y');

            // Clase del estado (puedes ajustar nombres según tu catálogo)
            $estadoNombre = optional($tarea->estado)->nombre ?? '—';
            $estadoClass = match (mb_strtolower($estadoNombre)) {
                'programada', 'pendiente' => 'bg-info',
                'en progreso', 'wip' => 'bg-warning',
                'finalizada', 'completada' => 'bg-success',
                'bloqueada' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp

        <div class="card border-0 shadow rounded w-75 mx-auto">
            <div class="card-body p-5" style="background-color:#ffffff;">

                {{-- Información general --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B; ">Información general
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="text-muted small mb-1">Nombre de la tarea:</div>
                        <div class="ps-4" style="color:#335;">
                            {{ $tarea->titulo }}
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="me-3 text-muted small">Estado:</div>
                        <span class="badge rounded {{ $estadoClass }} px-4 py-2">
                            {{ $estadoNombre }}
                        </span>
                    </div>
                </div>

                {{-- Cronograma (izq: creación / der: entrega) --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Cronograma</div>
                <div class="row mb-4">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <div class="text-muted small">Fecha de creación:</div>
                        <div class="ps-4 mt-2">{{ $creado }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Fecha de entrega:</div>
                        <div class="ps-4 mt-2">{{ $entrega ?? '—' }}</div>
                    </div>
                </div>

                {{-- Gestión de tiempo (izq) + Estado (der como pill) --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Gestión de tiempo</div>
                <div class="row mb-4 align-items-center">
                    <div class="col-md-12 mb-3 mb-md-0">
                        <div class="text-muted small">Tiempo estimado (h):</div>
                        <div class="ps-4 mt-2">{{ number_format($tarea->tiempo_estimado_h ?? 0, 0) }}</div>
                    </div>
                </div>

                {{-- Asignación: Izq área / Der colaborador --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Asignación</div>
                <div class="row mb-1">
                    <div class="col-md-8">
                        <div class="text-muted small">Área asignada:</div>
                        <div class="ps-4 mt-2">{{ optional($tarea->area)->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Colaborador:</div>
                        <div class="ps-4 mt-2">{{ optional($tarea->usuario)->name ?? '—' }}</div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="mt-4">
                    <div class="text-muted small mb-2">Descripción:</div>
                    <div class="ps-4 quill-content">{!! $tarea->descripcion !!}</div>
                </div>

            </div>
        </div>

        {{-- (Opcional) recursos, historial, time logs… podrías seccionarlos aquí abajo --}}
        {{-- @include('configuracion.servicios.tareas._recursos', ['tarea'=>$tarea]) --}}
        {{-- @include('configuracion.servicios.tareas._historial', ['tarea'=>$tarea]) --}}
        {{-- @include('configuracion.servicios.tareas._timelogs', ['tarea'=>$tarea]) --}}
    </x-slot>
</x-app-layout>