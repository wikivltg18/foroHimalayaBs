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

        @php
            /**
             * Badge Bootstrap para estados: Activo / Terminado
             */
            $estadoBadgeClass = function (?string $estadoNombre): string {
                $n = mb_strtolower(trim($estadoNombre ?? ''));
                return match ($n) {
                    'Activo' => 'bg-success',
                    'Terminado' => 'bg-secondary',
                    default => 'bg-secondary',
                };
            };
        @endphp

        @foreach ($tableros as $t)
                @php
                    $nombreServicio = $t->nombre_del_servicio
                        ?? ($t->servicio->nombre_servicio ?? $t->servicio->nombre_del_servicio ?? 'Servicio');
                    $tipoServicio = $t->nombre_tipo_de_servicio
                        ?? optional($t->servicio->tipo_servicio)->nombre
                        ?? 'Tipo de servicio';
                    $fechaCreacion = dtz($t->created_at, 'd/m/Y H:i');

                    $estadoNombre = $t->estado->nombre ?? '—';
                @endphp

                <div class="card rounded shadow border-0 mb-3">
                    {{-- ENCABEZADO (estilo del ejemplo) --}}
                    <div class="px-3 pt-3">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                            <div>
                                <div class="d-flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-grid-1x2-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M0 1a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm9 0a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1zm0 9a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1z" />
                                    </svg>
                                    <span class="fw-bold px-1">Tablero {{ $t->nombre_del_tablero }}</span>
                                </div>
                                <p class="mb-1">Nombre del servicio: {{ $nombreServicio }}</p>
                                <p class="mb-1 text-muted">
                                    <span class="fw-semibold">Tipo del servicio:</span> {{ $tipoServicio }}
                                </p>
                                <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
                                    <div class="d-flex flex-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-calendar" viewBox="0 0 16 16">
                                            <path
                                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                                        </svg>
                                        <span class="px-1"> {{ $fechaCreacion }}</span>
                                    </div>

                                    <span class="badge rounded-pill {{ $estadoBadgeClass($estadoNombre) }}">
                                        {{ $estadoNombre }}
                                    </span>


                                </div>
                            </div>

                            <div class="mt-3 mt-md-0">
                                <a href="{{ route('configuracion.servicios.tableros.show', [
                'cliente' => $cliente->id,
                'servicio' => $t->servicio_id,
                'tablero' => $t->id
            ]) }}" class="btn btn-primary rounded-pill d-inline-flex align-items-center px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                                        <path
                                            d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
                                    </svg>
                                    <span class="ms-2">Ver</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Columnas/Fases (scroll horizontal) --}}
                    <div class="card-body d-flex flex-row gap-2 overflow-auto">
                        @forelse($t->columnas as $col)
                            <div class="card flex-shrink-0" style="min-width: 240px; background-color: aliceblue;">
                                <div class="card-body rounded">
                                    <p class="text-primary fw-bold text-center small mb-0">Fase: {{ $col->posicion }}</p>
                                    <h6 class="card-title fw-bold text-center">
                                        {{ $col->nombre_de_la_columna }}
                                    </h6>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Este tablero aún no tiene columnas.</div>
                        @endforelse
                    </div>
                </div>
        @endforeach

        <div class="d-flex justify-content-end mt-3">
            {{ $tableros->links('pagination::bootstrap-4') }}
        </div>

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