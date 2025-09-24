<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('clientes.index') }}" class="btn btn-primary">Volver</a>
    </x-slot>

    <x-slot name="titulo">
        Lista de tableros
    </x-slot>

    <x-slot name="slot">

        {{-- Barra de controles --}}
        <form method="GET" class="row g-2 align-items-center mb-3">
            <div class="col-sm-6 col-md-4">
                <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control"
                    placeholder="Búsqueda (cliente, servicio, tablero)">
            </div>
            <div class="col-sm-3 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([5, 10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected($perPage == $n)>Mostrar {{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3 col-md-2">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="created_at" @selected($sort == 'created_at')>Ordenar por: Fecha</option>
                    <option value="cliente" @selected($sort == 'cliente')>Cliente</option>
                    <option value="servicio" @selected($sort == 'servicio')>Servicio</option>
                    <option value="modalidad" @selected($sort == 'modalidad')>Modalidad</option>
                    <option value="tipo" @selected($sort == 'tipo')>Tipo</option>
                    <option value="estado" @selected($sort == 'estado')>Estado</option>
                </select>
            </div>
            <div class="col-sm-3 col-md-2">
                <select name="dir" class="form-select" onchange="this.form.submit()">
                    <option value="asc" @selected($dir == 'asc')>Ascendente</option>
                    <option value="desc" @selected($dir == 'desc')>Descendente</option>
                </select>
            </div>
            <div class="col-sm-12 col-md-2 text-md-end">
                <button class="btn btn-outline-secondary w-100">Aplicar</button>
            </div>
        </form>

        <div class="card rounded shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="text-center">
                            <tr>
                                <th>Cliente</th>
                                <th>Nombre del Tablero</th>
                                <th>Modalidad del Servicio</th>
                                <th>Tipo de servicio</th>
                                <th>Estado del tablero</th>
                                <th>Consolidado</th>
                                <th>Tableros</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($tableros as $t)
                                                        @php
                                                            $clienteNombre = $t->nombre_cliente ?? ($t->cliente->nombre ?? '—');
                                                            $servNombre = $t->nombre_del_servicio
                                                                ?? ($t->servicio->nombre_servicio ?? $t->servicio->nombre_del_servicio ?? '—');

                                                            $tipoObj = $t->servicio->tipo ?? ($t->servicio->tipo_servicio ?? null);
                                                            $tipoNombre = $t->nombre_tipo_de_servicio ?? ($tipoObj->nombre ?? '—');
                                                            $modalNombre = $t->nombre_modalidad ?? optional($t->servicio->modalidad)->nombre ?? '—';
                                                            $estadoNombre = $t->estado->nombre ?? '—';

                                                            $estadoClass = match (strtolower($estadoNombre)) {
                                                                'activo' => 'bg-success',
                                                                'terminado' => 'bg-secondary',
                                                                'en pausa', 'pausado' => 'bg-warning text-dark',
                                                                default => 'bg-info'
                                                            };
                                                        @endphp
                                                        <tr class="{{ $loop->odd ? 'table-light' : '' }}">
                                                            <td>{{ $clienteNombre }}</td>
                                                            <td>{{ $servNombre }}</td>
                                                            <td>{{ $modalNombre }}</td>
                                                            <td>{{ $tipoNombre }}</td>
                                                            <td>
                                                                <span class="badge rounded-pill {{ $estadoClass }}" style="font-weight:600;">
                                                                    {{ $estadoNombre }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                {{-- Ajusta a tu ruta real de consolidado --}}
                                                                <a href="{{ url('/servicios/' . $t->servicio_id . '/consolidado') }}"
                                                                    class="btn btn-info btn-sm rounded-pill">
                                                                    Ver consolidado
                                                                </a>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('configuracion.servicios.tableros.show', [
                                    'cliente' => $t->cliente_id,
                                    'servicio' => $t->servicio_id,
                                    'tablero' => $t->id
                                ]) }}" class="btn btn-primary btn-sm rounded-pill">
                                                                    Ver tablero
                                                                </a>
                                                            </td>
                                                            <td class="text-center">
                                                                <form action="{{ route('configuracion.servicios.tableros.destroy', [
                                    'cliente' => $t->cliente_id,
                                    'servicio' => $t->servicio_id,
                                    'tablero' => $t->id
                                ]) }}" method="POST"
                                                                    onsubmit="return confirm('¿Seguro que deseas eliminar este tablero?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                                                        Eliminar
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted p-4">No hay tableros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $tableros->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    </x-slot>
</x-app-layout>