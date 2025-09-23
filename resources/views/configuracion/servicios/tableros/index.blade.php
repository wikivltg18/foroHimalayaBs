<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('config.servicios.create', $cliente->id) }}" class="btn btn-primary me-2">
            Crear configuración
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-primary me-2">Volver</a>
    </x-slot>

    <x-slot name="titulo">
        Configuración de servicios
    </x-slot>

    <x-slot name="slot">
        @if (session('success'))
            <script>
                Swal.fire({
                    title: '¡Éxito!',
                    text: {!! json_encode(session('success')) !!},
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
            </script>
        @endif
        @if($tableros->isEmpty())
            <div class="alert alert-info">Este cliente aún no tiene tableros creados.</div>
        @endif

        @foreach ($tableros as $t)
                @php
                    $nombreServicio = $t->nombre_del_servicio
                        ?? ($t->servicio->nombre_servicio ?? $t->servicio->nombre_del_servicio ?? 'Servicio');
                    $tipoServicio = $t->nombre_tipo_de_servicio
                        ?? optional($t->servicio->tipo_servicio)->nombre
                        ?? 'Tipo de servicio';
                    $fechaCreacion = optional($t->created_at)?->format('d \\- M \\- Y');
                @endphp

                <div class="card rounded shadow border-0 mb-3">
                    <div class="p-3 rounded">
                        <div class="row">
                            <div class="col-10">
                                <p class="fw-bold m-0">Nombre del servicio: {{ $nombreServicio }}</p>
                                <p class="fw-bold m-0">Tipo del servicio: {{ $tipoServicio }}</p>
                                <p class="fw-ligth m-0 text-muted">
                                    Fecha de creación: {{ $fechaCreacion }}
                                    · Estado: <span class="badge bg-success">{{ $t->estado->nombre ?? '—' }}</span>
                                </p>
                                <p class="m-0 text-muted">Tablero: {{ $t->nombre_del_tablero }}</p>
                            </div>
                            <div class="col-2 d-flex justify-content-center align-items-center">
                                <a href="{{ route('configuracion.servicios.tableros.show', [
                'cliente' => $cliente->id,
                'servicio' => $t->servicio_id,
                'tablero' => $t->id
            ]) }}" class="btn btn-primary me-2 px-5">
                                    Ver
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Columnas/Fases (scroll horizontal) --}}
                    <div class="card-body d-flex flex-row gap-2 overflow-auto">
                        @forelse($t->columnas as $col)
                            <div class="card flex-shrink-0" style="min-width: 240px;">
                                <div class="card-body rounded" style="background-color: #003B7B;">
                                    <p class="text-white-50 text-center small mb-0">Fase: {{ $col->posicion }}</p>
                                    <h5 class="card-title text-white fw-bold text-center">
                                        {{ $col->nombre_de_la_columna }}
                                    </h5>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Este tablero aún no tiene columnas.</div>
                        @endforelse
                    </div>
                </div>
        @endforeach

        @if($tableros->hasPages())
            <div>
                {{ $tableros->links() }}
            </div>
        @endif

    </x-slot>

    {{-- ...tu vista tal cual... --}}

    @push('scripts')
        <script>
            (function () {
                // Helper con fallback si no está SweetAlert2
                function showAlert(opts) {
                    if (window.Swal && typeof Swal.fire === 'function') {
                        return Swal.fire(opts);
                    } else {
                        const msg = (opts.title ? opts.title + ': ' : '') + (opts.text || (opts.html ? (new DOMParser().parseFromString(opts.html, 'text/html').body.textContent || '') : ''));
                        alert(msg);
                        return Promise.resolve();
                    }
                }

                // Mensajes de sesión
                @if(session('success'))
                    showAlert({
                        title: '¡Éxito!',
                        text: {!! json_encode(session('success')) !!},
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                @endif

                @if(session('error'))
                    showAlert({
                        title: 'Error',
                        text: {!! json_encode(session('error')) !!},
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                @endif

                @if(session('warning'))
                    showAlert({
                        title: 'Atención',
                        text: {!! json_encode(session('warning')) !!},
                        icon: 'warning',
                        confirmButtonText: 'Ok'
                    });
                @endif

                @if(session('info'))
                    showAlert({
                        title: 'Información',
                        text: {!! json_encode(session('info')) !!},
                        icon: 'info',
                        confirmButtonText: 'Ok'
                    });
                @endif

                @if ($errors->any())
                    showAlert({
                        title: 'Revisa los campos',
                        html: `{!! collect($errors->all())->map(fn($e) => '<div class="text-start">• ' . e($e) . '</div>')->implode('') !!}`,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                @endif
                })();
        </script>
    @endpush

</x-app-layout>