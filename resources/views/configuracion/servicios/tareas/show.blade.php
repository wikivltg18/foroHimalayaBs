{{-- resources/views/configuracion/servicios/tareas/show.blade.php --}}
<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('configuracion.servicios.tableros.show', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-secondary me-2">
            Volver al tablero
            <a class="btn btn-primary btn-pill me-2" href="{{ route('tareas.editInColumn', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id,
]) }}">
                Editar
            </a>

            <form method="POST" class="d-inline" action="{{ route('tareas.destroyInColumn', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id,
]) }}" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-pill">
                    Eliminar
                </button>
            </form>


    </x-slot>

    <x-slot name="titulo">
        {{ $tarea->titulo }}
    </x-slot>

    <x-slot name="slot">
        @php
            $horasReales = $tarea->timeLogs->sum('duracion_h');
            $creado = optional($tarea->created_at)?->format('d/m/Y g:i a');
            $entrega = optional($tarea->fecha_de_entrega)?->format('d/m/Y');

            $estadoNombre = optional($tarea->estado)->nombre ?? '—';
            $estadoClass = match (mb_strtolower($estadoNombre)) {
                'programada', 'pendiente' => 'bg-info',
                'en progreso', 'wip' => 'bg-warning',
                'finalizada', 'completada' => 'bg-success',
                'bloqueada' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp

        <style>
            .quill-content img {
                max-width: 100%;
                height: auto;
                display: inline-block;
            }

            .quill-content .ql-align-center {
                text-align: center;
            }

            .quill-content .ql-align-right {
                text-align: right;
            }

            .quill-content .ql-align-justify {
                text-align: justify;
            }

            .quill-content blockquote {
                border-left: 4px solid #e0e0e0;
                margin: .5rem 0;
                padding: .25rem .75rem;
                color: #555;
            }

            .quill-content pre {
                background: #f6f8fa;
                padding: .75rem;
                border-radius: .25rem;
                overflow-x: auto;
            }

            .quill-gallery img {
                width: 100%;
                height: auto;
                border-radius: .5rem;
            }

            .link-favicon {
                width: 16px;
                height: 16px;
                vertical-align: -2px;
                margin-right: .5rem;
                border-radius: 3px;
            }
        </style>

        <div class="card border-0 shadow rounded w-75 mx-auto">
            <div class="card-body p-5" style="background-color:#ffffff;">

                {{-- Información general --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Información general</div>
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="text-muted small mb-1">Nombre de la tarea:</div>
                        <div class="ps-4" style="color:#335;">{{ $tarea->titulo }}</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="me-3 text-muted small">Estado:</div>
                        <span class="badge rounded {{ $estadoClass }} px-4 py-2">{{ $estadoNombre }}</span>
                    </div>
                </div>

                {{-- Cronograma --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Cronograma</div>
                <div class="row mb-4">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <div class="text-muted small">Fecha de creación:</div>
                        <div class="ps-4 mt-2">{{ $creado }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Fecha de entrega:</div>
                        <div class="ps-4 mt-2">{{ $entrega ?? '—' }}</div>
                    </div>
                </div>

                {{-- Gestión de tiempo --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Gestión de tiempo</div>
                <div class="row mb-4 align-items-center">
                    <div class="col-md-12 mb-3 mb-md-0">
                        <div class="text-muted small">Tiempo estimado (h):</div>
                        <div class="ps-4 mt-2">{{ number_format($tarea->tiempo_estimado_h ?? 0, 0) }}</div>
                    </div>
                </div>

                {{-- Asignación --}}
                <div class="h5 fw-bold mb-3" style="color:#003B7B;">Asignación</div>
                <div class="row mb-1">
                    <div class="col-md-8">
                        <div class="text-muted small">Área asignada:</div>
                        <div class="ps-4 mt-2">{{ optional($tarea->area)->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Colaborador:</div>
                        <div class="ps-4 mt-2">{{ optional($tarea->usuario)->name ?? '—' }}</div>
                    </div>
                </div>

                {{-- Descripción (HTML Quill) --}}
                <div class="mt-4">
                    <div class="text-muted small mb-2">Descripción:</div>
                    <div class="ps-4 quill-content">{!! $tarea->descripcion !!}</div>
                </div>

                @php
                    $imagenes = $tarea->recursos()->where('tipo', 'image')->orderBy('orden')->get();
                    $links = $tarea->recursos()->where('tipo', 'link')->orderBy('orden')->get();

                    // Resolver URL relativa para mostrar (respeta host/puerto actuales)
                    $resolverUrlImagen = function ($pathOrUrl) {
                        $val = (string) ($pathOrUrl ?? '');
                        $val = str_replace('\\', '/', $val);
                        if (\Illuminate\Support\Str::startsWith($val, ['http://', 'https://', '//', '/storage/'])) {
                            return $val;
                        }
                        $publicRoot = str_replace('\\', '/', public_path());
                        if (\Illuminate\Support\Str::startsWith($val, $publicRoot)) {
                            $val = \Illuminate\Support\Str::after($val, $publicRoot);
                        }
                        $val = '/' . ltrim($val, '/');
                        if (\Illuminate\Support\Str::startsWith($val, '/tareas/')) {
                            return url('/storage' . $val, [], false);
                        }
                        $val = ltrim($val, '/');
                        return url('/storage/' . $val, [], false);
                    };
                @endphp

                {{-- Adjuntos (imágenes) --}}
                @if($imagenes->isNotEmpty())
                    <div class="mt-4">
                        <div class="text-muted small mb-2">Adjuntos:</div>
                        <div class="row g-4 ps-4">
                            @foreach($imagenes as $img)
                                @php
                                    $raw = $img->url ?? $img->ruta ?? '';
                                    $src = $resolverUrlImagen($raw);
                                @endphp
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <img src="{{ $src }}" alt="{{ $img->titulo ?? 'Adjunto' }}" class="shadow-sm w-100 rounded">
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <div class="small text-muted me-2 flex-grow-1 text-truncate">
                                            {{ $img->titulo ?? basename(parse_url($src, PHP_URL_PATH)) }}
                                        </div>
                                        {{-- Botón Descargar (vía controlador para forzar attachment) --}}
                                        <a href="{{ route('tareas.recursos.download', ['tarea' => $tarea->id, 'recurso' => $img->id]) }}"
                                            class="btn btn-sm btn-outline-secondary" title="Descargar">
                                            Descargar
                                        </a>
                                        {{-- Alternativa simple (misma URL de la imagen con atributo download)
                                        <a href="{{ $src }}" download
                                            class="btn btn-sm btn-outline-secondary ms-2">Descargar</a>
                                        --}}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Enlaces --}}
                @if($links->isNotEmpty())
                    <div class="mt-3 ps-4">
                        <div class="text-muted small mb-2">Enlaces:</div>
                        <ul class="mb-0">
                            @foreach($links as $lnk)
                                @php
                                    $href = $lnk->url ?? $lnk->enlace ?? '';
                                    $host = $href ? parse_url($href, PHP_URL_HOST) : null;
                                    $favicon = $host ? 'https://www.google.com/s2/favicons?sz=32&domain=' . $host : null;
                                @endphp
                                @if(!empty($href))
                                    <li class="mb-1">
                                        @if($favicon)
                                            <img class="link-favicon" src="{{ $favicon }}" alt="favicon">
                                        @endif
                                        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer">
                                            {{ $lnk->titulo ?: $href }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        </div>

        {{-- @include('configuracion.servicios.tareas._historial', ['tarea'=>$tarea]) --}}
        {{-- @include('configuracion.servicios.tareas._timelogs', ['tarea'=>$tarea]) --}}
    </x-slot>
</x-app-layout>