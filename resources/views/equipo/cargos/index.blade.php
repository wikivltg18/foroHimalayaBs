<x-app-layout>
    <x-slot name="buttonPress">
        {{-- Botón para crear una nueva cargo --}}
        <a href="{{ url('/equipo/cargos/create') }}" class="btn btn-primary">Crear cargo</a>
    </x-slot>

    <!-- Título principal -->
    <x-slot name="titulo">
        Lista de Cargos
    </x-slot>

    <x-slot name="slot">
        {{-- Contenedor principal --}}
        <div class="row">
            <form method="GET" action="{{ route('equipo.cargos.index') }}" class="row g-2 align-items-center mb-3 justify-content-end" id="form-filtro-cargo">
                <div class="col-auto">
                    <input type="text" name="buscar" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar cargo...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </form>
            <div class="col-md-12">
                {{-- Evalua si la respuesta cargos esta vacio --}}
                @if ($cargos->isEmpty())
                    <p>No hay cargos registradas.</p>
                @endif
            <div class="table-responsive">
                    <table id="data-table-roles" class="table table-striped table-hover table-bordered text-nowrap">
                        <thead class="text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripcion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($cargos as $cargo)
                                <tr>
                                    <td>{{ $cargo->nombre }}</td>
                                    <td>{{ $cargo->descripcion }}</td>
                                    <td>
                                        {{-- Botones para editar y eliminar --}}
                                        <a href="{{ route('equipo.cargos.edit', $cargo->id) }}" class="btn btn-warning"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
</svg></a>
                                        <form action="{{ route('equipo.cargos.destroy', $cargo->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
  <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
</svg></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Páginado --}}
                    <div class="d-flex justify-content-end mt-3">
                        {{ $cargos->links('pagination::bootstrap-4') }}
                    </div>
            </div>
        </div>
    </x-slot>
    @section('alert')
    <script>
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
        function limpiarFormulario() {
                const form = document.getElementById('form-filtro-cargo');
                form.querySelector('input[name="buscar"]').value = '';
                form.submit();
            }
    </script>
@endsection
</x-app-layout>