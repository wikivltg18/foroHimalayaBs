<x-app-layout>

    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/roles') }}" class="btn btn-primary">Listado de áreas</a>
    </x-slot>

<!-- Título principal -->
    <x-slot name="titulo">
        Editar de Rol
    </x-slot>

    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        <div class="row" style="height: 360px;">
            <!-- Columna izquierda: Formulario -->
            <div class="col-md-6">
                <form action="{{ route('equipo.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                    <div class="form-group mb-3">
                        <label for="name">Nombre del rol <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') form-control-warning @enderror"
                            value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <small class="text-warning">{{ $message }}</small>
                        @else
                            <small class="text-muted">Ejemplo: Marketing Digital, Seguridad web</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100">Actualizar rol</button>
                </form>
            </div>
            {{-- Imagen --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B; height: 100%;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="logo_himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </x-slot>
</x-app-layout>