<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">Crear cliente</a>
    </x-slot>

    <x-slot name="titulo">
        Lista de clientes
    </x-slot>

    <x-slot name="slot">
        <div class="row">
            {{-- Filtro de clientes --}}
            <form method="GET" action="{{ route('clientes.index') }}"
                class="row g-2 align-items-center mb-3 justify-content-end" id="form-filtro-clientes">
                <div class="col-auto">
                    <select name="cantidad" class="form-select" onchange="this.form.submit()">
                        <option value="5" {{ request('cantidad') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('cantidad') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('cantidad') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('cantidad') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control"
                        placeholder="Buscar cliente...">
                </div>
                <div class="col-auto">
                    <select name="estado" class="form-select">
                        <option value="" disabled selected>Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="suspendido" {{ request('estado') == 'suspendido' ? 'selected' : '' }}>Suspendido
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </form>
            {{-- Tabla de clientes --}}
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered text-nowrap">
                        <thead class="text-center">
                            <tr>
                                <th class="px-3">Logo</th>
                                <th>Nombre</th>
                                <th>Web</th>
                                <th>Correo electrónico</th>
                                <th>Teléfono</th>
                                <th>Director ejecutivo</th>
                                <th>Estado</th>
                                <th>Contratos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($clientes->isEmpty())
                                <td class="text-muted" colspan="9"> No hay clientes registrados.</td>
                            @endif
                            @foreach ($clientes as $cliente)
                                <tr>
                                    <td><img src="{{ asset('storage/' . $cliente->logo) }}" alt="Logo del cliente"
                                            class="img-perfil rounded-circle"></td>
                                    <td>{{ $cliente->nombre }}</td>
                                    <td><a class="btn btn-primary" href="{{ $cliente->sitio_web }}"
                                            target="blank">{{ $cliente->nombre }}</a></td>
                                    <td>{{ $cliente->correo_electronico }}</td>
                                    <td>{{ $cliente->telefono }}</td>
                                    <td>{{ $cliente->usuario->name }}</td>
                                    <td>{{ $cliente->estado->nombre ?? 'Sin estado' }}</td>
                                    <td>
                                        @forelse ($cliente->tiposContrato as $contrato)
                                            @if ($contrato->id === 1)
                                                <a class="btn btn-dark mb-1"
                                                    href="{{ route('config.equipo.index', $cliente->id) }}">{{ $contrato->nombre }}</a>
                                            @elseif ($contrato->id === 2)
                                                <a class="btn btn-secondary mb-1"
                                                    href="{{ route('config.servicios.index', $cliente->id) }}">{{ $contrato->nombre }}</a>
                                            @endif
                                        @empty
                                            <span class="text-muted">Sin contratos</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                <path fill-rule="evenodd"
                                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST"
                                            class="form-eliminar" style="display:inline;" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                                                    <path
                                                        d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Paginación de clientes --}}

                    <div class="d-flex justify-content-end mt-3">
                        {{ $clientes->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
    @section('alert')
        <script>

            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('.form-eliminar');

                forms.forEach(form => {
                    form.addEventListener('submit', async function (e) {
                        e.preventDefault(); // Evita el envío inmediato

                        const result = await Swal.fire({
                            title: '¿Estás seguro?',
                            text: "Esta acción eliminará el cliente.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        });

                        if (result.isConfirmed) {
                            form.submit(); // Envía el formulario si se confirma
                        }
                    });
                });
            });

            // Mostrar alerta de éxito si hay un mensaje de éxito en la sesión
            document.addEventListener('DOMContentLoaded', function () {
                @if (session('success'))
                    Swal.fire({
                        title: '¡Éxito!',
                        text: '{{ session('success') }}',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                @endif
                        });


            // Función para limpiar el formulario de filtro
            function limpiarFormulario() {
                const form = document.getElementById('form-filtro-clientes');
                form.querySelector('input[name="buscar"]').value = '';
                form.querySelector('select[name="estado"]').selectedIndex = 0;
                form.submit();
            }
        </script>
    @endsection
</x-app-layout>