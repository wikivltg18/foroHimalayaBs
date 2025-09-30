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
            $creado  = dtz($tarea->created_at, 'd/m/Y g:i a');
            $entrega = dtz($tarea->fecha_de_entrega, 'd/m/Y');

            $estadoNombre = optional($tarea->estado)->nombre ?? '—';
            $estadoClass = match (mb_strtolower($estadoNombre)) {
                'programada', 'pendiente' => 'bg-info',
                'en progreso', 'wip'      => 'bg-warning',
                'finalizada', 'completada'=> 'bg-success',
                'bloqueada'               => 'bg-danger',
                default                   => 'bg-secondary',
            };
        @endphp

        <style>
            .quill-content img { max-width: 100%; height: auto; display: inline-block; }
            .quill-content .ql-align-center { text-align: center; }
            .quill-content .ql-align-right  { text-align: right; }
            .quill-content .ql-align-justify{ text-align: justify; }
            .quill-content blockquote { border-left: 4px solid #e0e0e0; margin: .5rem 0; padding: .25rem .75rem; color: #555; }
            .quill-content pre { background: #f6f8fa; padding: .75rem; border-radius: .25rem; overflow-x: auto; }
            .quill-gallery img { width: 100%; height: auto; border-radius: .5rem; }
            .link-favicon { width: 16px; height: 16px; vertical-align: -2px; margin-right: .5rem; border-radius: 3px; }
            .task-scroll { max-height: calc(100vh - 220px); overflow: auto; }
            .avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
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

                <div class="comentarios">
                    <div class="mb-2 fw-semibold" style="color:#003B7B;">Comentarios y Actividades</div>

                    @php
                        $horasReales = (float) $tarea->timeLogs->sum('duracion_h');
                        $fechaFinal = dtz($tarea->finalizada_at, 'd/m/Y g:i a');
                        $finalizada = (bool) $tarea->finalizada_at;
                        $disabled   = $finalizada ? 'disabled' : '';
                    @endphp

                    <div class="border rounded-3 p-3 p-md-4 mb-4" style="background:#fafbfc; border-color:#edf1f5;">
                        <form id="form-estado-tiempo" method="POST" action="{{ route('tareas.updateEstadoTiempo', [
                            'cliente' => $cliente->id,
                            'servicio' => $servicio->id,
                            'tablero' => $tablero->id,
                            'columna' => $columna->id,
                            'tarea' => $tarea->id
                        ]) }}">
                            @csrf
                            @method('PUT')

                            {{-- Fila superior: Estado / Tiempo real usado --}}
                            <div class="row g-3 align-items-end">
                                {{-- Estado --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-2" style="color:#003B7B;">Actualizar estado / tiempo</label>
                                    <select name="estado_id" class="form-select" {{ $finalizada ? 'disabled' : '' }}>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}" @selected(old('estado_id', $tarea->estado_id) == $estado->id)>
                                                {{ $estado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($finalizada)
                                        <input type="hidden" name="estado_id" value="{{ $tarea->estado_id }}">
                                    @endif
                                </div>

                                {{-- Tiempo real usado --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-2" style="color:#003B7B;">Tiempo real usado</label>
                                    <div class="input-group">
                                        <input type="number" name="duracion_real_h" min="0" step="0.25"
                                               class="form-control" placeholder="0.00" {{ $disabled }}>
                                        <span class="input-group-text">h</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Editor de comentarios (Quill) dentro del MISMO formulario --}}
                            <div class="mb-2 fw-semibold" style="color:#003B7B;">Comentario (obligatorio para guardar)</div>
                            <div class="border rounded mb-3">
                                <div id="comment-editor" style="height: 180px;"
                                     data-upload-url="{{ route('quill.upload') }}"
                                     data-csrf-token="{{ csrf_token() }}">
                                </div>
                                <input type="hidden" name="comentario_html" id="comentario_html">
                                <input type="file" id="quill-comment-image-input" accept="image/*" class="d-none">
                            </div>

                            {{-- Fila inferior: Fecha completada / total horas / Guardar --}}
                            <div class="row align-items-center g-3">
                                {{-- Fecha completada --}}
                                <div class="col-md-4">
                                    <div class="text-muted mb-1">Fecha de tarea completada</div>
                                    <input type="text" class="form-control" disabled value="{{ $fechaFinal ?: '' }}"
                                           readonly style="background:#f1f2f4; border-color:#e3e6ea; color:#5b6570;">
                                    @if(!$fechaFinal)
                                        <small class="text-muted">Se completa automáticamente al pasar a un estado final.</small>
                                    @endif
                                </div>

                                {{-- Total horas (visual) --}}
                                <div class="col-md-4 text-md-center">
                                    <p class="mb-1">Horas dedicadas</p>
                                    <div class="fs-3 fw-semibold" style="line-height:1;">
                                        {{ number_format($horasReales, 2) }} <span class="fs-6 fw-normal">horas</span>
                                    </div>
                                </div>

                                {{-- Botón único --}}
                                <div class="col-md-4 d-flex flex-column align-items-md-end">
                                    <button id="btn-guardar" class="btn btn-primary"
                                            style="background-color:#003B7B;border-color:#003B7B;"
                                            type="submit" {{ $disabled }}>
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Comentarios reales --}}
                    <div class="vstack gap-4 mt-4">
                        @forelse($tarea->comentarios as $c)
                            <div class="d-flex align-items-start gap-3">
                                @php
                                    /** @var \App\Models\User|null $autor */
                                    $autor = $c->autor;

                                    $foto = null;
                                    if ($autor) {
                                        if (!empty($autor->profile_photo_url ?? null)) {
                                            $foto = $autor->profile_photo_url;
                                        } elseif (!empty($autor->profile_photo_path ?? null)) {
                                            $foto = \Illuminate\Support\Facades\Storage::url($autor->profile_photo_path);
                                        } elseif (!empty($autor->foto_perfil ?? null)) {
                                            $val = (string) $autor->foto_perfil;
                                            $foto = \Illuminate\Support\Str::startsWith($val, ['http://','https://','/storage/'])
                                                ? $val : \Illuminate\Support\Facades\Storage::url($val);
                                        } elseif (!empty($autor->avatar ?? null)) {
                                            $foto = $autor->avatar;
                                        } elseif (!empty($autor->photo_url ?? null)) {
                                            $foto = $autor->photo_url;
                                        }
                                    }

                                    $gravatar = null;
                                    if (!empty($autor?->email)) {
                                        $hash = md5(strtolower(trim($autor->email)));
                                        $gravatar = "https://www.gravatar.com/avatar/{$hash}?s=72&d=identicon";
                                    }

                                    $inicial = strtoupper(mb_substr($autor->name ?? 'U', 0, 1));
                                @endphp

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
                                        <small class="text-muted">• {{ dtz($c->created_at,'d M Y, H:i') }}</small>
                                    </div>

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
                    const area   = form.dataset.area || '—';
                    const estado = form.dataset.estado || '—';

                    (window.Swal?.fire ? Swal.fire({
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
                    }) : (function(){ if(confirm('¿Eliminar?')) HTMLFormElement.prototype.submit.call(form);})());
                }

                // Pre-submit: volcar el HTML del editor al input hidden
                function onUnifiedFormSubmit(e) {
                    const form = e.target.closest && e.target.closest('#form-estado-tiempo');
                    if (!form) return;

                    const editor = document.querySelector('#comment-editor .ql-editor');
                    const out    = document.getElementById('comentario_html');
                    if (editor && out) out.value = editor.innerHTML.trim();
                }

                function init() {
                    document.removeEventListener('submit', onDeleteSubmit, true);
                    document.addEventListener('submit', onDeleteSubmit, true);

                    document.removeEventListener('submit', onUnifiedFormSubmit, true);
                    document.addEventListener('submit', onUnifiedFormSubmit, true);
                }

                ['DOMContentLoaded', 'turbo:load', 'livewire:load'].forEach(evt =>
                    document.addEventListener(evt, init)
                );
                if (document.readyState === 'interactive' || document.readyState === 'complete') init();
            })();
        </script>

        <script>
        (function () {
            const form         = document.getElementById('form-estado-tiempo');
            if (!form) return;

            const selectEstado = form.querySelector('select[name="estado_id"]');
            const inputHoras   = form.querySelector('input[name="duracion_real_h"]');
            const btnGuardar   = document.getElementById('btn-guardar');
            const hiddenHtml   = document.getElementById('comentario_html');

            // === Datos del servidor ===
            const INITIAL_STATE_ID      = {{ (int) $tarea->estado_id }};
            const CURRENT_IS_PROGRAMADA = {{ in_array(mb_strtolower(optional($tarea->estado)->nombre ?? ''), ['programada','pendiente']) ? 'true' : 'false' }};

            // --- Helpers Editor ---
            function getEditorEl() {
                return document.querySelector('#comment-editor .ql-editor');
            }
            function getEditorHtml() {
                const el = getEditorEl();
                return el ? el.innerHTML.trim() : '';
            }
            function getEditorPlainText() {
                const el = getEditorEl();
                const txt = el ? el.innerText.replace(/\u00A0/g, ' ').trim() : '';
                return txt;
            }
            function hasMeaningfulComment() {
                const text = getEditorPlainText();
                if (text.length > 0) return true;

                const html = getEditorHtml()
                    .replace(/<p><br><\/p>/gi, '')
                    .replace(/<br\s*\/?>/gi, '')
                    .replace(/&nbsp;/gi, ' ')
                    .replace(/<\/?[^>]+(>|$)/g, '')
                    .trim();

                return html.length > 0;
            }

            // --- Helpers Estado / Horas ---
            function estadoChanged() {
                if (!selectEstado) return false;
                const val = parseInt(selectEstado.value, 10);
                return !Number.isNaN(val) && val !== INITIAL_STATE_ID;
            }
            function horasOk() {
                if (!inputHoras) return false;
                const val = parseFloat(inputHoras.value || '0');
                return !Number.isNaN(val) && val > 0;
            }
            function estadoNuevoNombreLower() {
                const opt = selectEstado?.selectedOptions?.[0];
                return opt ? opt.textContent.trim().toLowerCase() : '';
            }

            // === Reglas de habilitación del botón ===
            // Comentario SIEMPRE requerido
            // - Si estado actual es Programada/Pendiente: comentario + (cambio de estado) Y (horas > 0)
            // - En otros estados: comentario + (cambio de estado O horas > 0)
            function puedeGuardar() {
                if (!hasMeaningfulComment()) return false;

                if (CURRENT_IS_PROGRAMADA) {
                    return estadoChanged() && horasOk();
                } else {
                    return estadoChanged() || horasOk();
                }
            }

            function updateButtonState() {
                if (btnGuardar) btnGuardar.disabled = !puedeGuardar();
            }

            function onSubmit(e) {
                if (hiddenHtml) hiddenHtml.value = getEditorHtml();

                if (!puedeGuardar()) {
                    e.preventDefault();

                    let msg;
                    if (!hasMeaningfulComment()) {
                        msg = 'Debes escribir un comentario para guardar la actualización.';
                    } else if (CURRENT_IS_PROGRAMADA) {
                        msg = 'La tarea está en "Programada". Debes cambiar el estado y asignar horas (> 0 h), además del comentario.';
                    } else {
                        msg = 'Debes cambiar el estado o asignar horas (> 0 h), además del comentario.';
                    }

                    if (window.Swal?.fire) {
                        Swal.fire({ icon: 'warning', title: 'Acción requerida', text: msg, confirmButtonText: 'Entendido' });
                    } else {
                        alert(msg);
                    }
                }
            }

            // === Enganche de listeners ===
            form.addEventListener('submit', onSubmit);
            if (selectEstado) selectEstado.addEventListener('change', updateButtonState);
            if (inputHoras)   inputHoras.addEventListener('input', updateButtonState);

            // Espera a que Quill monte el editor para observar cambios
            function attachEditorListenersOnce() {
                const el = getEditorEl();
                if (!el) return false;

                el.addEventListener('input', updateButtonState);
                const obs = new MutationObserver(updateButtonState);
                obs.observe(el, { childList: true, subtree: true, characterData: true });

                return true;
            }

            // Polling ligero (máx 3s) para engancharse a Quill
            let waited = 0;
            const iv = setInterval(() => {
                if (attachEditorListenersOnce()) {
                    clearInterval(iv);
                    updateButtonState();
                } else {
                    waited += 100;
                    if (waited >= 3000) {
                        clearInterval(iv);
                        updateButtonState();
                    }
                }
            }, 100);

            updateButtonState();
        })();
        </script>
    @endpush
</x-app-layout>
