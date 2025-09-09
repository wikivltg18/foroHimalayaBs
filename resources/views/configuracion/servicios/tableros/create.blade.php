<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('config.servicios.index', ['cliente' => $cliente->id]) }}" class="btn btn-primary">Volver</a>
    </x-slot>

    <x-slot name="titulo">
        Crear Tablero de Servicio
    </x-slot>

    <x-slot name="slot">
        <div class="col-md-12 m-md-1">
            <h6 class="text-white p-2 rounded" style="background-color:#003B7B">
                <strong>Nuevo Tablero</strong>
            </h6>
        </div>

        {{-- Bloque de errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('configuracion.servicios.tableros.store', ['cliente' => $cliente->id, 'servicio' => $servicio->id]) }}"
            method="POST" class="card shadow-sm">
            @csrf
            <div class="card-body">
                <!-- Datos del tablero -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre_del_tablero" class="form-label">Nombre del Tablero *</label>
                        <input type="text" class="form-control" id="nombre_del_tablero" name="nombre_del_tablero"
                            value="{{ old('nombre_del_tablero') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="estado_tablero_id" class="form-label">Estado del Tablero *</label>
                        <select class="form-select" id="estado_tablero_id" name="estado_tablero_id" required>
                            <option value="">Seleccione un estado</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}" @selected(old('estado_tablero_id') == $estado->id)>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Columnas del tablero (Fases del servicio) -->
                <div class="mb-3">
                    <label class="form-label">Fases del servicio que serán columnas del tablero:</label>
                    <div class="row g-3">
                        @forelse($fasesInstancias as $index => $fase)
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">
                                            {{ $fase->plantilla->nombre ?? $fase->nombre }}
                                        </h6>
                                        @php
                                            $descFase = $fase->plantilla->descripcion ?? $fase->descripcion;
                                        @endphp
                                        @if($descFase)
                                            <p class="card-text small text-muted">{{ $descFase }}</p>
                                        @endif

                                        <!-- Campos ocultos para cada fase -->
                                        <input type="hidden" name="columnas[{{ $index }}][nombre]"
                                            value="{{ $fase->plantilla->nombre ?? $fase->nombre }}">
                                        <input type="hidden" name="columnas[{{ $index }}][descripcion]"
                                            value="{{ $descFase }}">
                                        <input type="hidden" name="columnas[{{ $index }}][orden]"
                                            value="{{ $fase->posicion }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    Este servicio aún no tiene fases configuradas.
                                    Por favor, configure primero las fases del servicio.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Campos opcionales descriptivos (si los quieres enviar) -->
                <input type="hidden" name="nombre_del_servicio"
                    value="{{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }}">
                <input type="hidden" name="nombre_cliente" value="{{ $cliente->nombre }}">
                <input type="hidden" name="nombre_modalidad" value="{{ $servicio->modalidad->nombre ?? '' }}">
                <input type="hidden" name="nombre_tipo_de_servicio"
                    value="{{ $servicio->tipo_servicio->nombre ?? '' }}">
            </div>

            @php $sinFases = $fasesInstancias->isEmpty(); @endphp
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" @disabled($sinFases)>
                    Crear Tablero
                </button>
            </div>
        </form>
    </x-slot>

    @section('alert')
        <script>
            @if(session('success'))
                Swal.fire({
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
            @endif
        </script>
    @endsection
</x-app-layout>