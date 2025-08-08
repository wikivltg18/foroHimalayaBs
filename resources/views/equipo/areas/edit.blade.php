<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/areas') }}" class="btn btn-primary">Listado de áreas</a>
    </x-slot>

    <!-- Título principal -->
    <x-slot name="titulo">
        Editar de área
    </x-slot>

    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        <div class="row" style="height: 360px;">
            <!-- Columna izquierda: Formulario -->
            <div class="col-md-6">
                <form action="{{ route('equipo.areas.update', $area->id) }}" method="POST">
                @csrf
                @method('PUT')

                    <div class="form-group mb-3">
                        <label for="area1">Nombre del área <span class="text-danger">*</span></label>
                        <input type="text" id="area1" name="nombre" class="form-control @error('nombre') form-control-warning @enderror"
                            value="{{ old('nombre', $area->nombre) }}" required>
                        @error('nombre')
                            <small class="text-warning">{{ $message }}</small>
                        @else
                            <small class="text-muted">Ejemplo: Marketing Digital, Seguridad web</small>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="area2">Descripción del área <span class="text-danger">*</span></label>
                        <input type="text" id="area2" name="descripcion" class="form-control @error('descripcion') form-control-warning @enderror"
                            value="{{ old('descripcion', $area->descripcion) }}" required>
                        @error('descripcion')
                            <small class="text-warning">{{ $message }}</small>
                        @else
                            <small class="text-muted">Ejemplo: Área técnica, creativa, etc.</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100">Actualizar área</button>
                </form>
            </div>
            {{-- Imagen --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B; height: 100%;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="logo_himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </x-slot>
</x-app-layout>