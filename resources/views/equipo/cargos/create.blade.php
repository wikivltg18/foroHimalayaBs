<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/cargos') }}" class="btn btn-primary">Listado de cargos</a>
    </x-slot>

    <!-- Título principal -->
    <x-slot name="titulo">
        Crear de cargo
    </x-slot>

    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        <div class="row" style="height: 360px;">
            <!-- Columna izquierda: Formulario -->
            <div class="col-md-6">
                <form action="{{ route('equipo.cargos.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="cargo1">Nombre del cargo <span class="text-danger">*</span></label>
                        <input type="text" id="cargo1" class="form-control @error('nombre') form-control-warning @enderror" name="nombre" required value="{{ old('nombre') }}">
                        @error('nombre')
                            <small class="text-warning"> {{ $message }} </small>
                        @else
                            <small class="text-muted">Ejemplo: Diseñador, Desarrollo</small>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="cargo2">Descripción del cargo <span class="text-danger">*</span></label>
                        <input type="text" id="cargo2" class="form-control @error('descripcion') form-control-warning @enderror" name="descripcion" value="{{ old('descripcion') }}">
                        @error('descripcion')
                            <small class="text-warning"> {{ $message }} </small>
                        @else
                            <small class="text-muted">Ejemplo: Cargo profesional responsable de la gestión y organización, etc.</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar nuevo cargo</button>
                </form>
            </div>
            <!-- Columna derecha: Imagen -->
            <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B; height: 100%;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="logo_himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </x-slot>
</x-app-layout>