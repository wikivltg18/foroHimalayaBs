<x-app-layout>
    <x-slot name="titulo">
        Foro de Tareas
    </x-slot>

    <div class="container-fluid">
        <!-- Filtros de búsqueda -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form action="{{ route('foro.index') }}" method="GET" class="d-flex">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de tarea o cliente..." 
                               value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('foro.index') }}" class="btn btn-outline-secondary ms-2">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabla de Tareas -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Nombre de la tarea</th>
                                <th>Fecha de creación</th>
                                <th>Fecha de entrega</th>
                                <th>Estado</th>
                                <th class="text-center">Enlace Tarea</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tareas as $tarea)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $cliente = $tarea->columna->tablero->cliente ?? null;
                                            @endphp
                                            @if($cliente && $cliente->logo)
                                                <img src="{{ asset('storage/' . $cliente->logo) }}" 
                                                     alt="Logo" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                            @endif
                                            <span>{{ $cliente->nombre ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $tarea->titulo }}</span>
                                    </td>
                                    <td>
                                        {{ $tarea->created_at->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $tarea->fecha_de_entrega ? $tarea->fecha_de_entrega->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $tarea->estado->color ?? '#6c757d' }}; color: #fff;">
                                            {{ $tarea->estado->nombre ?? 'Sin estado' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($tarea->columna && $tarea->columna->tablero)
                                            <a href="{{ route('tareas.show', [
                                                'cliente' => $tarea->columna->tablero->cliente_id,
                                                'servicio' => $tarea->columna->tablero->servicio_id,
                                                'tablero' => $tarea->columna->tablero->id,
                                                'columna' => $tarea->columna->id,
                                                'tarea' => $tarea->id
                                            ]) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">Sin ubicación</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No se encontraron tareas asignadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $tareas->appends(request()->query())->links('pagination::bootstrap-4') }}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>