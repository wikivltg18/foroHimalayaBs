<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/areas') }}" class="btn btn-primary">Listado de áreas</a>
    </x-slot>
    <!-- Título principal -->
    <x-slot name="titulo">
        Crear de área
    </x-slot>
    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        <div class="row" style="height: 360px;">
            <!-- Columna izquierda: Formulario -->
            <div class="col-md-6">
                <form action="{{ route('equipo.areas.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="area1">Nombre del área <span class="text-danger">*</span></label>
                        <input type="text" id="area1" class="form-control @error('nombre') form-control-warning @enderror" name="nombre" required value="{{ old('nombre') }}">
                        @error('nombre')
                            <small class="text-warning"> {{ $message }} </small>
                        @else
                            <small class="text-muted">Ejemplo: Marketing Digital, Seguridad web</small>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="area2">Descripción del área <span class="text-danger">*</span></label>
                        <input type="text" id="area2" class="form-control @error('descripcion') form-control-warning @enderror" name="descripcion" value="{{ old('descripcion') }}">
                        @error('descripcion')
                            <small class="text-warning"> {{ $message }} </small>
                        @else
                            <small class="text-muted">Ejemplo: Área técnica, creativa, etc.</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar nueva área</button>
                </form>
            </div>
            <!-- Columna derecha: Imagen -->
            <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B; height: 100%;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="logo_himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </x-slot>
</x-app-layout>