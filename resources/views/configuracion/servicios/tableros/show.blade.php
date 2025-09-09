<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('configuracion.servicios.tableros.index', ['cliente' => $cliente->id]) }}"
            class="btn btn-secondary me-2">
            Volver a tableros
        </a>
        <a href="{{ route('configuracion.servicios.tableros.edit', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-primary">
            Editar tablero
        </a>
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
                            {{ $tablero->nombre_tipo_de_servicio ?? optional($servicio->tipo_servicio)->nombre ?? '—' }}
                            · Modalidad:
                            {{ $tablero->nombre_modalidad ?? optional($servicio->modalidad)->nombre ?? '—' }}
                        </p>
                        <p class="text-muted m-0">
                            Cliente: {{ $tablero->nombre_cliente ?? $cliente->nombre }}
                        </p>
                        <p class="text-muted m-0">
                            Creado: {{ optional($tablero->created_at)?->format('d/m/Y H:i') }}
                            · Estado: <span class="badge bg-secondary">{{ $tablero->estado->nombre ?? '—' }}</span>
                        </p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                        <a href="{{ route('configuracion.servicios.tableros.edit', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-outline-primary">
                            Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Lienzo tipo “kanban”: columnas horizontales --}}
            <div class="card-body">
                <div class="d-flex flex-row gap-3 overflow-auto pb-2">
                    @forelse($tablero->columnas as $col)
                        <div class="card flex-shrink-0 border-0 shadow-sm" style="min-width: 320px;">
                            <div class="card-header text-white fw-bold text-center" style="background-color:#003B7B;">
                                {{ $col->nombre_de_la_columna }}
                                <div class="small text-white-50">Orden: {{ $col->orden }}</div>
                            </div>
                            <div class="card-body" style="background:#F6F8FB;">
                                @if($col->descripcion)
                                    <p class="small text-muted">{{ $col->descripcion }}</p>
                                @endif

                                {{-- Aquí en el futuro podrías listar “tarjetas/tareas” de la columna --}}
                                <div class="text-center text-muted small">
                                    (Sin ítems aún)
                                </div>
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