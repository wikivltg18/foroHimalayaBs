{{-- resources/views/configuracion/servicios/tareas/show.blade.php --}}
<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('configuracion.servicios.tableros.show', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id
]) }}" class="btn btn-secondary me-2">
            Volver al tablero
        </a>

        <a class="btn btn-primary btn-pill me-2" href="{{ route('tareas.editInColumn', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id,
]) }}">
            Editar
        </a>

        <form method="POST" class="d-inline form-eliminar" action="{{ route('tareas.destroyInColumn', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id,
]) }}" data-titulo="{{ $tarea->titulo }}" data-area="{{ optional($tarea->area)->nombre ?? '—' }}"
            data-estado="{{ optional($tarea->estado)->nombre ?? '—' }}">
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

            .task-scroll {
                max-height: calc(100vh - 220px);
                overflow: auto;
            }

            .avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                object-fit: cover;
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
                    <div class="ps-4 task-scroll quill-content">{!! $tarea->descripcion !!}</div>
                </div>

                @php
                    $imagenes = $tarea->recursos()->where('tipo', 'image')->orderBy('orden')->get();
                    $links = $tarea->recursos()->where('tipo', 'link')->orderBy('orden')->get();

                    $resolverUrlImagen = function ($pathOrUrl) {
                        $val = (string) ($pathOrUrl ?? '');
                        $val = str_replace('\\', '/', $val);
                        if (\Illuminate\Support\Str::startsWith($val, ['http://', 'https://', '//', '/storage/']))
                            return $val;

                        $publicRoot = str_replace('\\', '/', public_path());
                        if (\Illuminate\Support\Str::startsWith($val, $publicRoot)) {
                            $val = \Illuminate\Support\Str::after($val, $publicRoot);
                        }
                        $val = '/' . ltrim($val, '/');
                        if (\Illuminate\Support\Str::startsWith($val, '/tareas/'))
                            return url('/storage' . $val, [], false);

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
                                        <a href="{{ route('tareas.recursos.download', ['tarea' => $tarea->id, 'recurso' => $img->id]) }}"
                                            class="btn btn-sm btn-outline-secondary" title="Descargar">
                                            Descargar
                                        </a>
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

                <hr>

                <div class="comentarios px-5">
                    <div class="mb-2 fw-semibold" style="color:#003B7B;">Comentarios y Actividades</div>



                    @php
                        $tz = 'America/Bogota';
                        $horasReales = (float) $tarea->timeLogs->sum('duracion_h');
                        $fechaFinal = $tarea->finalizada_at?->copy()?->timezone($tz)?->format('d/m/Y g:i a');
                    @endphp

                    <div class="border rounded-3 p-3 p-md-4 mb-4" style="background:#fafbfc; border-color:#edf1f5;">
                        <div class="row g-4 align-items-end">
                            {{-- Fecha de tarea completada --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-2" style="color:#003B7B;">Fecha de tarea
                                    completada</label>
                                <input type="text" class="form-control" disabled value="{{ $fechaFinal ?: '' }}"
                                    readonly style="background:#f1f2f4; border-color:#e3e6ea; color:#5b6570;">
                                @if(!$fechaFinal)
                                    <small class="text-muted">Se completa automáticamente al pasar a un estado
                                        final.</small>
                                @endif
                            </div>

                            {{-- Tiempo real usado (h) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-2" style="color:#003B7B;">Tiempo real
                                    usado</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge rounded-pill px-3 py-2"
                                        style="background:#f1f2f4; color:#2b2f33; font-weight:600; font-size:.95rem;">
                                        {{ number_format($horasReales, 2) }}
                                    </span>
                                    <span class="ms-2">horas</span>
                                </div>
                                <small class="text-muted">Suma de todos los registros de tiempo de la tarea.</small>
                            </div>

                            {{-- Actualizar estado y registrar tiempo rápido --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-2" style="color:#003B7B;">Actualizar estado /
                                    tiempo</label>

                                <form method="POST" action="{{ route('tareas.updateEstadoTiempo', [
    'cliente' => $cliente->id,
    'servicio' => $servicio->id,
    'tablero' => $tablero->id,
    'columna' => $columna->id,
    'tarea' => $tarea->id
]) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-2">
                                        <select name="estado_id" class="form-select" {{ $tarea->finalizada_at ? 'disabled' : '' }}>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id }}" @selected($tarea->estado_id == $estado->id)>
                                                    {{ $estado->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($tarea->finalizada_at)
                                            <input type="hidden" name="estado_id" value="{{ $tarea->estado_id }}">
                                            <small class="text-muted">La tarea ya está finalizada.</small>
                                        @endif
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="number" name="duracion_real_h" min="0" step="0.25"
                                                class="form-control" placeholder="0.00 h">
                                        </div>
                                        <div class="col-7">
                                            <input type="text" name="nota_tiempo" maxlength="500" class="form-control"
                                                placeholder="Nota (opcional)">
                                        </div>
                                    </div>

                                    <div class="d-grid mt-2">
                                        <button class="btn btn-primary" type="submit"
                                            style="background-color:#003B7B;border-color:#003B7B;">
                                            Guardar
                                        </button>
                                    </div>

                                    <small class="text-muted d-block mt-1">
                                        Si ingresas horas, se registrará un time log desde ahora hacia atrás.
                                    </small>
                                </form>
                            </div>
                        </div>
                    </div>



                    {{-- Editor de comentarios (Quill se inicializa en resources/js/app.js) --}}
                    <form id="formComentario" method="POST"
                        action="{{ route('tareas.comentarios.store', $tarea->id) }}">
                        @csrf
                        <div class="border rounded mb-3">
                            <div id="comment-editor" style="height: 180px;"
                                data-upload-url="{{ route('quill.upload') }}" data-csrf-token="{{ csrf_token() }}">
                            </div>

                            <input type="hidden" name="comentario_html" id="comentario_html">
                            <input type="file" id="quill-comment-image-input" accept="image/*" class="d-none">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mb-4">
                            <button type="submit" class="btn btn-primary" id="btnGuardarComentario"
                                style="background-color:#003B7B;border-color:#003B7B;">
                                Comentar
                            </button>
                        </div>
                    </form>

                    {{-- Comentarios reales --}}
                    <div class="vstack gap-4 mt-4">
                        @forelse($tarea->comentarios as $c)
                            <div class="d-flex align-items-start gap-3">
                                {{-- Avatar (inicial si no hay foto) --}}
                                @php
                                    /** @var \App\Models\User|null $autor */
                                    $autor = $c->autor;

                                    // 1) Detectar la mejor URL de foto disponible
                                    $foto = null;
                                    if ($autor) {
                                        if (!empty($autor->profile_photo_url ?? null)) {
                                            $foto = $autor->profile_photo_url;
                                        } elseif (!empty($autor->profile_photo_path ?? null)) {
                                            $foto = \Illuminate\Support\Facades\Storage::url($autor->profile_photo_path);
                                        } elseif (!empty($autor->foto_perfil ?? null)) { // tu campo propio
                                            $val = (string) $autor->foto_perfil;
                                            $foto = \Illuminate\Support\Str::startsWith($val, ['http://', 'https://', '/storage/'])
                                                ? $val
                                                : \Illuminate\Support\Facades\Storage::url($val);
                                        } elseif (!empty($autor->avatar ?? null)) {
                                            $foto = $autor->avatar;
                                        } elseif (!empty($autor->photo_url ?? null)) {
                                            $foto = $autor->photo_url;
                                        }
                                    }

                                    // 2) Gravatar como respaldo si hay email
                                    $gravatar = null;
                                    if (!empty($autor?->email)) {
                                        $hash = md5(strtolower(trim($autor->email)));
                                        $gravatar = "https://www.gravatar.com/avatar/{$hash}?s=72&d=identicon";
                                    }

                                    // 3) Inicial para el fallback visual
                                    $inicial = strtoupper(mb_substr($autor->name ?? 'U', 0, 1));
                                @endphp

                                {{-- Render: foto si existe; si la imagen falla, cae a gravatar (o default). Si no hay nada,
                                inicial. --}}
                                @if($foto || $gravatar)
                                    <img src="{{ $foto ?: $gravatar }}" alt="Foto de {{ $autor->name ?? 'usuario' }}"
                                        class="avatar" width="36" height="36" loading="lazy"
                                        onerror="this.onerror=null; this.src='{{ $gravatar ?: asset('images/default-profile.png') }}';">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                                        style="width:36px;height:36px;">
                                        {{ $inicial }}
                                    </div>
                                @endif


                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong>{{ optional($c->autor)->name ?? 'Usuario' }}</strong>
                                        <small class="text-muted">•
                                            {{ optional($c->created_at)->format('d M Y, H:i') }}</small>

                                        @if(auth()->check() && ($puedeBorrarComentarios || (int) $c->usuario_id === (int) auth()->id()))
                                            <form method="POST"
                                                action="{{ route('tareas.comentarios.destroy', [$tarea->id, $c->id]) }}"
                                                class="ms-auto form-eliminar-comentario"
                                                data-autor="{{ optional($c->autor)->name ?? 'Usuario' }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>

                                    {{-- Contenido del comentario (HTML sanitizado en servidor) --}}
                                    <div class="quill-content mt-2">{!! $c->comentario !!}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Aún no hay comentarios. ¡Sé el primero en comentar!</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- @include('configuracion.servicios.tareas._historial', ['tarea'=>$tarea]) --}}
        {{-- @include('configuracion.servicios.tareas._timelogs', ['tarea'=>$tarea]) --}}
    </x-slot>

    {{-- Scripts locales (no Quill; Quill se maneja en resources/js/app.js) --}}
    @push('scripts')
        <script>
            (function () {
                // Confirmación para eliminar tarea
                function onDeleteSubmit(e) {
                    const form = e.target.closest && e.target.closest('form.form-eliminar');
                    if (!form) return;
                    e.preventDefault();

                    const titulo = form.dataset.titulo || 'esta tarea';
                    const area = form.dataset.area || '—';
                    const estado = form.dataset.estado || '—';

                    Swal.fire({
                        title: `¿Estás seguro de eliminar la tarea “${titulo}”?`,
                        html: `<div style="text-align:left;">
                                                                                                <p>Esta acción es <b>permanente</b> y <b>no se puede deshacer</b>.</p>
                                                                                                <hr><p><b>Área:</b> ${area}</p><p><b>Estado:</b> ${estado}</p>
                                                                                               </div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            const btn = form.querySelector('button[type="submit"]');
                            if (btn) { btn.disabled = true; btn.textContent = 'Eliminando…'; }
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                }

                // Confirmación para eliminar comentario
                function onDeleteCommentSubmit(e) {
                    const form = e.target.closest && e.target.closest('form.form-eliminar-comentario');
                    if (!form) return;
                    e.preventDefault();

                    const autor = form.dataset.autor || 'este usuario';

                    Swal.fire({
                        title: `¿Eliminar comentario de ${autor}?`,
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            const btn = form.querySelector('button[type="submit"]');
                            if (btn) { btn.disabled = true; btn.textContent = 'Eliminando…'; }
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                }

                function init() {
                    document.removeEventListener('submit', onDeleteSubmit, true);
                    document.addEventListener('submit', onDeleteSubmit, true);

                    document.removeEventListener('submit', onDeleteCommentSubmit, true);
                    document.addEventListener('submit', onDeleteCommentSubmit, true);
                }

                ['DOMContentLoaded', 'turbo:load', 'livewire:load'].forEach(evt =>
                    document.addEventListener(evt, init)
                );
                if (document.readyState === 'interactive' || document.readyState === 'complete') init();
            })();
        </script>
    @endpush
</x-app-layout>