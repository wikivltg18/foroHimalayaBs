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
                        <p class="text-muted m-0">
                            Creado: {{ optional($tablero->created_at)?->format('d/m/Y H:i') }}
                            · Estado: <span class="badge bg-success">{{ $tablero->estado->nombre ?? '—' }}</span>
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
                                                                                        <span class="badge bg-secondary">
                                                                                            {{ optional($tarea->estado)->nombre ?? '—' }}
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
                                                <a id="addTask-{{ $col->id }}" class="btn btn-sm btn-outline-primary w-100 mt-2" href="{{ route('tareas.createInColumn', [
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

    @section('alert')
        <script></script>
    @endsection
</x-app-layout>