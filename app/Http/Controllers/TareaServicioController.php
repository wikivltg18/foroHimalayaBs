<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Area;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\EstadoTarea;
use Illuminate\Support\Str;
use App\Models\TareaRecurso;
use App\Models\TareaTimeLog;
use Illuminate\Http\Request;
use App\Models\TareaServicio;
use App\Models\TableroServicio;
use App\Models\TareaComentario;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use App\Models\TareaEstadoHistorial;
use Illuminate\Support\Facades\Auth;
use App\Models\ColumnaTableroServicio;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreTareaRequest;
use App\Notifications\NotificacionAsignacionTarea;
use App\Notifications\NotificacionComentarioTarea;
use App\Notifications\NotificacionTareaFinalizada;

class TareaServicioController extends Controller
{
    public function index()
    {
        $tareas = TareaServicio::with(['area', 'usuario', 'estado', 'columna'])->get();
        return view('configuracion.servicios.tableros.show', compact('tareas'));
    }

    public function create(Cliente $cliente, Servicio $servicio, TableroServicio $tablero, ColumnaTableroServicio $columna)
    {
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Áreas contratadas para este servicio (horas_contratadas > 0)
        $areas   = $servicio->areasContratadas()->orderBy('areas.nombre')->get();
        $estados = EstadoTarea::orderBy('nombre')->get();

        return view('configuracion.servicios.tareas.create', compact(
            'areas',
            'estados',
            'cliente',
            'servicio',
            'tablero',
            'columna'
        ));
    }

    public function store(Request $request, Cliente $cliente, Servicio $servicio, TableroServicio $tablero, ColumnaTableroServicio $columna)
    {
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        $areaIdsValidas = $servicio->areaIdsContratadas();

        $validated = $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'estado_id'         => ['required', Rule::exists('estado_tarea', 'id')],
            'area_id'           => ['required', 'integer', Rule::in($areaIdsValidas->all())],
            'usuario_id'        => ['required', Rule::exists('users', 'id')],
            'descripcion'       => ['required', 'string'],
            'tiempo_estimado_h' => ['required', 'numeric', 'min:0'],
            'fecha_de_entrega'  => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        // ========== QUILL: Sanitización mejorada ==========
        // Permite clases/estilos de Quill, iframes seguros y data-uri para imágenes pegadas
        $validated['descripcion'] = Purifier::clean($validated['descripcion'], [
            'HTML.Trusted'             => true,
            'HTML.SafeIframe'          => true,
            'URI.SafeIframeRegexp'     => '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%',
            'HTML.Allowed'             => implode(',', [
                'p','b','strong','i','em','u','s','strike','blockquote','pre','code',
                'ul','ol','li',
                'a[href|target|rel]',
                'br',
                'span[style|class]',
                'div[style|class]',
                'h1','h2','h3','h4','h5','h6',
                'img[src|alt|width|height]',
                'iframe[src|width|height|frameborder|allowfullscreen]',
            ]),
            // comas (,) no punto y coma (;)
            'CSS.AllowedProperties'    => 'color,background-color,text-align,font-weight,font-style,text-decoration,margin-left,margin-right',
            'Attr.AllowedClasses'      => 'ql-align-center ql-align-right ql-align-justify ql-indent-1 ql-indent-2 ql-indent-3 ql-size-small ql-size-large ql-size-huge',
            'Attr.AllowedFrameTargets' => ['_blank','_self'],
            'URI.AllowedSchemes'       => ['http','https','data'],
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ]);
        // ========== /QUILL ==========

        $nextPos = TareaServicio::where('columna_id', $columna->id)->max('posicion');
        $nextPos = is_null($nextPos) ? 1 : $nextPos + 1;

        $tarea = DB::transaction(function () use ($validated, $columna, $nextPos) {
            $tarea = TareaServicio::create([
                'id'                => (string) Str::uuid(),
                'columna_id'        => $columna->id,
                'estado_id'         => $validated['estado_id'],
                'area_id'           => $validated['area_id'],
                'usuario_id'        => $validated['usuario_id'],
                'titulo'            => $validated['titulo'],
                'descripcion'       => $validated['descripcion'], // con URLs de draft o data:
                'tiempo_estimado_h' => $validated['tiempo_estimado_h'],
                'fecha_de_entrega'  => $validated['fecha_de_entrega'] ?? null,
                'posicion'          => $nextPos,
                'archivada'         => false,
            ]);

            TareaEstadoHistorial::create([
                'id'                 => (string) Str::uuid(),
                'tarea_id'           => $tarea->id,
                'cambiado_por'       => Auth::id() ?? $validated['usuario_id'],
                'estado_id_anterior' => null,
                'estado_id_nuevo'    => $validated['estado_id'],
                'observacion'        => 'Creación de tarea',
            ]);

            return $tarea;
        });

        // 👉 Post-proceso: mover adjuntos y registrar recursos
        $descripcionNueva = $this->syncRecursosDesdeDescripcion($tarea, $request->session()->getId());
        if ($descripcionNueva !== $tarea->descripcion) {
            $tarea->update(['descripcion' => $descripcionNueva]);
        }

                $tarea->loadMissing('area', 'columna.tablero.cliente');

        $user = User::find($validated['usuario_id']);
        if ($user) {
            $user->notify(new NotificacionAsignacionTarea($tarea));

        }


        return redirect()->route('configuracion.servicios.tableros.show', [
            'cliente'  => $cliente->id,
            'servicio' => $servicio->id,
            'tablero'  => $tablero->id,
        ])->with('success', "Tarea «{$tarea->titulo}» creada exitosamente.");
    }

    /**
     * Mueve archivos subidos a draft/{sessionId} → tareas/{tarea_id},
     * crea registros en tarea_recursos, convierte data-uri a archivo
     * y reescribe las URLs en el HTML (fragmento, sin <html>/<body>).
     */
    private function syncRecursosDesdeDescripcion(TareaServicio $tarea, string $sessionId): string
{
    $html = $tarea->descripcion ?? '';
    if ($html === '') return $html;

    $publicBase  = rtrim(Storage::disk('public')->url(''), '/');   // /storage
    $draftPrefix = "{$publicBase}/tareas/draft/{$sessionId}/";

    // Cargar DOM como fragmento con un root temporal
    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<div id="__root__">' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    $root = $dom->getElementById('__root__');
    $changed = false;

    // Límite seguro para la columna 'titulo' (ajústalo a tu schema real)
    $tituloMax = 191; // si tu columna es VARCHAR(191); cambia si usas otro tamaño

    // Helper para truncar títulos (respeta multibyte)
    $limitTitulo = function (?string $txt) use ($tituloMax) {
        $txt = trim((string) $txt);
        if ($txt === '') return '';
        return Str::limit($txt, $tituloMax, '…');
    };

    // --- Imágenes data: (pegadas/arrastradas en Quill) ---
    foreach ($dom->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        if (!$src || strpos($src, 'data:') !== 0) continue;

        if (!preg_match('#^data:(.+?);base64,(.+)$#', $src, $m)) continue;
        $mime = $m[1];
        $data = base64_decode($m[2], true);
        if ($data === false) continue;

        $extMap = [
            'image/jpeg'   => 'jpg',
            'image/jpg'    => 'jpg',
            'image/png'    => 'png',
            'image/gif'    => 'gif',
            'image/webp'   => 'webp',
            'image/svg+xml'=> 'svg',
        ];
        $ext = $extMap[$mime] ?? 'bin';

        $finalDir  = "tareas/{$tarea->id}";
        $filename  = 'img-' . Str::uuid()->toString() . '.' . $ext;
        $finalPath = "{$finalDir}/{$filename}";

        Storage::disk('public')->makeDirectory($finalDir);
        Storage::disk('public')->put($finalPath, $data);

        $absPath   = Storage::disk('public')->path($finalPath);
        $hash      = file_exists($absPath) ? hash_file('sha256', $absPath) : null;
        $size      = Storage::disk('public')->size($finalPath) ?? 0;
        $publicUrl = Storage::disk('public')->url($finalPath);

        TareaRecurso::create([
            'id'          => (string) Str::uuid(),
            'tarea_id'    => $tarea->id,
            'tipo'        => 'image',
            'titulo'      => $limitTitulo($img->getAttribute('alt') ?: pathinfo($finalPath, PATHINFO_FILENAME)),
            'url'         => $publicUrl,
            'path'        => $finalPath,
            'mime'        => $mime,
            'size_bytes'  => $size,
            'hash_sha256' => $hash,
            'orden'       => 1,
        ]);

        $img->setAttribute('src', $publicUrl);
        $changed = true;
    }

    // 1) Imágenes <img> que apuntan a /tareas/draft/{session}
    foreach ($dom->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        if (!$src || strpos($src, $draftPrefix) !== 0) continue;

        $relativeDraft = ltrim(str_replace($publicBase . '/', '', $src), '/'); // tareas/draft/{session}/file.ext
        if (!Storage::disk('public')->exists($relativeDraft)) continue;

        $filename  = basename($relativeDraft);
        $finalDir  = "tareas/{$tarea->id}";
        $finalPath = "{$finalDir}/{$filename}";

        if (Storage::disk('public')->exists($finalPath)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext  = pathinfo($filename, PATHINFO_EXTENSION);
            $finalPath = "{$finalDir}/{$name}-" . Str::random(5) . ".{$ext}";
        }

        Storage::disk('public')->makeDirectory($finalDir);
        Storage::disk('public')->move($relativeDraft, $finalPath);

        $mime      = Storage::disk('public')->mimeType($finalPath) ?? 'application/octet-stream';
        $size      = Storage::disk('public')->size($finalPath) ?? 0;
        $absPath   = Storage::disk('public')->path($finalPath);
        $hash      = file_exists($absPath) ? hash_file('sha256', $absPath) : null;
        $publicUrl = Storage::disk('public')->url($finalPath);

        TareaRecurso::create([
            'id'          => (string) Str::uuid(),
            'tarea_id'    => $tarea->id,
            'tipo'        => 'image', // según tu ENUM: 'image','file','link'
            'titulo'      => $limitTitulo($img->getAttribute('alt') ?: pathinfo($finalPath, PATHINFO_FILENAME)),
            'url'         => $publicUrl,
            'path'        => $finalPath,
            'mime'        => $mime,
            'size_bytes'  => $size,
            'hash_sha256' => $hash,
            'orden'       => 1,
        ]);

        $img->setAttribute('src', $publicUrl);
        $changed = true;
    }

    // 2) Enlaces <a>: registrar como recurso 'link' usando el texto visible (y filtrar internos)
    $appUrl  = rtrim(config('app.url') ?? '', '/');
    $appHost = '';
    if ($appUrl) {
        $pu = parse_url($appUrl);
        $appHost = $pu['host'] ?? '';
    }

    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = trim($a->getAttribute('href') ?? '');
        if ($href === '') continue;

        $hrefLower = strtolower($href);
        // Ignorar anchors/pseudo-enlaces
        if ($hrefLower === '#' || strpos($hrefLower, 'javascript:') === 0) continue;

        // Evita registrar como "link" los archivos internos del propio storage de tareas
        if (strpos($href, $publicBase . '/tareas/') === 0) continue;

        // Ignorar enlaces internos a la propia app (localhost/127.0.0.1 o mismo host)
        $hHost = parse_url($href, PHP_URL_HOST);
        if ($hHost) {
            if (in_array($hHost, ['127.0.0.1', 'localhost'], true)) continue;
            if ($appHost && $hHost === $appHost) continue;
        }

        // Título desde el texto del anchor; si está vacío, usa hostname o 'Enlace'
        $anchorText = trim($a->textContent ?? '');
        if ($anchorText === '') {
            $anchorText = $hHost ?: 'Enlace';
        }

        $titulo = $limitTitulo($anchorText);
        if ($titulo === '') $titulo = 'Enlace';

        // Evitar duplicados exactos por URL
        $exists = $tarea->recursos()->where('tipo', 'link')->where('url', $href)->exists();
        if ($exists) continue;

        TareaRecurso::create([
            'id'       => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'tipo'     => 'link',
            'titulo'   => $titulo,
            'url'      => $href,
            'orden'    => 1,
        ]);
    }

    // Devolver fragmento (sin <html>/<body>)
    $newHtml = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $newHtml .= $dom->saveHTML($child);
    }
    return $changed ? $newHtml : $html;
}


    public function show(
    Request $request,
    Cliente $cliente,
    Servicio $servicio,
    TableroServicio $tablero,
    ColumnaTableroServicio $columna,
    TareaServicio $tarea
) {
    // Validar cadena jerárquica (anti ID tampering)
    abort_unless(optional($tarea->columna)->id === $columna->id, 404);
    abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
    abort_unless($tablero->servicio_id === $servicio->id, 404);
    abort_unless($servicio->cliente_id === $cliente->id, 404);

    // Eager loading necesario (incluye comentarios con autor)
    $tarea->load([
        'estado',
        'area',
        'usuario',          // owner/creador
        'finalizador',      // quien la cerró (si aplica)
        'usuarios',         // asignados (pivot)
        'recursos' => fn ($q) => $q->orderBy('tipo')->orderBy('orden'),
        'timeLogs' => fn ($q) => $q->latest('started_at'),
        'historiales' => fn ($q) => $q->oldest('created_at'),
        'historiales.autor:id,name',
        'historiales.estadoDesde:id,nombre',
        'historiales.estadoHasta:id,nombre',
        'comentarios' => fn ($q) => $q->latest('created_at'),
        'comentarios.autor:id,name,email,foto_perfil',
        'columna.tablero',
        'columna.tablero.cliente',
    ]);

    $estados = EstadoTarea::orderBy('nombre')->get();
    // Bandera para evitar consultas extra en Blade al decidir si se puede borrar comentarios
    $puedeBorrarComentarios = false;
    if (auth()->check()) {
        $puedeBorrarComentarios =
            ((int) $tarea->usuario_id === (int) auth()->id()) ||
            $tarea->usuarios->contains(fn ($u) => (int) $u->id === (int) auth()->id());
    }

    return view('configuracion.servicios.tareas.show', [
        'cliente' => $cliente,
        'servicio' => $servicio,
        'tablero'  => $tablero,
        'columna'  => $columna,
        'tarea'    => $tarea,
        'estados'=> $estados,
        'puedeBorrarComentarios' => $puedeBorrarComentarios,
    ]);
}


    public function usuariosPorArea(Area $area, Request $request)
    {
        $servicioId = $request->query('servicio_id');

        if (!$servicioId) {
            return response()->json(['message' => 'servicio_id requerido'], 400);
        }

        // Valida que el AREA esté contratada para ese SERVICIO (horas_contratadas > 0)
        $contratada = DB::table('mapa_areas')
            ->join('mapa_del_cliente', 'mapa_del_cliente.id', '=', 'mapa_areas.mapa_del_cliente_id')
            ->where('mapa_del_cliente.servicio_id', $servicioId)
            ->where('mapa_areas.area_id', $area->id)
            ->where('mapa_areas.horas_contratadas', '>', 0)
            ->exists();

        if (!$contratada) {
            return response()->json([
                'message' => 'El área no está contratada para este servicio o no tiene horas.'
            ], 404);
        }

        // Devuelve los usuarios de esa área
        $users = $area->usuarios()->select('id', 'name')->orderBy('name')->get();

        return response()->json($users);
    }



    public function horasContratadasArea(Servicio $servicio, Area $area)
    {
        // Seguridad: solo áreas que realmente están mapeadas a este servicio con horas > 0
        $mapaId = optional($servicio->mapa)->id;
        if (!$mapaId) {
            return response()->json(['horas' => 0, 'message' => 'El servicio no tiene mapa configurado'], 200);
        }

        $horas = (float) DB::table('mapa_areas')
            ->where('mapa_del_cliente_id', $mapaId)
            ->where('area_id', $area->id)
            ->sum('horas_contratadas');

        return response()->json(['horas' => $horas]);
    }


    // app/Http/Controllers/TareaServicioController.php

public function edit(
    Cliente $cliente,
    Servicio $servicio,
    TableroServicio $tablero,
    ColumnaTableroServicio $columna,
    TareaServicio $tarea
) {
    // Validar jerarquía
    abort_unless(optional($tarea->columna)->id === $columna->id, 404);
    abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
    abort_unless($tablero->servicio_id === $servicio->id, 404);
    abort_unless($servicio->cliente_id === $cliente->id, 404);

    // Listas para selects
    $areas   = $servicio->areasContratadas()->orderBy('areas.nombre')->get();
    $estados = EstadoTarea::orderBy('nombre')->get();

    // Cargar relaciones mínimas para mostrar info en cabecera si quieres
    $tarea->load(['estado', 'area', 'usuario']);

    return view('configuracion.servicios.tareas.edit', compact(
        'areas',
        'estados',
        'cliente',
        'servicio',
        'tablero',
        'columna',
        'tarea'
    ));
}

public function update(
        Request $request,
        Cliente $cliente,
        Servicio $servicio,
        TableroServicio $tablero,
        ColumnaTableroServicio $columna,
        TareaServicio $tarea
    ) {
        abort_unless(optional($tarea->columna)->id === $columna->id, 404);
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        $areaIdsValidas = $servicio->areaIdsContratadas();

        $validated = $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'estado_id'         => ['required', Rule::exists('estado_tarea', 'id')],
            'area_id'           => ['required', 'integer', Rule::in($areaIdsValidas->all())],
            'usuario_id'        => ['required', Rule::exists('users', 'id')],
            'descripcion'       => ['required', 'string'],
            'tiempo_estimado_h' => ['required', 'numeric', 'min:0'],
            'fecha_de_entrega'  => ['nullable', 'date'],
        ]);

        // Sanitizar Quill
        $validated['descripcion'] = Purifier::clean($validated['descripcion'], [
            'HTML.Trusted'             => true,
            'HTML.SafeIframe'          => true,
            'URI.SafeIframeRegexp'     => '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%',
            'HTML.Allowed'             => implode(',', [
                'p','b','strong','i','em','u','s','strike','blockquote','pre','code',
                'ul','ol','li',
                'a[href|target|rel]',
                'br',
                'span[style|class]',
                'div[style|class]',
                'h1','h2','h3','h4','h5','h6',
                'img[src|alt|width|height]',
                'iframe[src|width|height|frameborder|allowfullscreen]',
            ]),
            'CSS.AllowedProperties'    => 'color,background-color,text-align,font-weight,font-style,text-decoration,margin-left,margin-right',
            'Attr.AllowedClasses'      => 'ql-align-center ql-align-right ql-align-justify ql-indent-1 ql-indent-2 ql-indent-3 ql-size-small ql-size-large ql-size-huge',
            'Attr.AllowedFrameTargets' => ['_blank','_self'],
            'URI.AllowedSchemes'       => ['http','https','data'],
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ]);

        $estadoAnterior = (int) $tarea->estado_id;
        $nuevoEstado    = (int) $validated['estado_id'];
        $usuarioAnteriorId = (int) $tarea->usuario_id;
        $fechaAnterior = $tarea->fecha_de_entrega;


        // === SOLO DESDE EDICIÓN ===
        $solicitaReactivar   = $request->boolean('reactivar'); // viene del checkbox de la vista edit
        $eraFinal            = !is_null($tarea->finalizada_at);
        // Si NO quieres reapertura automática por cambio de estado, deja en false:
        $reaperturaPorEstado = $eraFinal && !in_array($nuevoEstado, EstadoTarea::finalIds(), true);

        DB::transaction(function () use ($tarea, $validated, $estadoAnterior, $nuevoEstado, $solicitaReactivar, $reaperturaPorEstado) {
            // 1) Actualiza campos base
            $tarea->update([
                'estado_id'         => $nuevoEstado,
                'area_id'           => $validated['area_id'],
                'usuario_id'        => $validated['usuario_id'],
                'titulo'            => $validated['titulo'],
                'descripcion'       => $validated['descripcion'],
                'tiempo_estimado_h' => $validated['tiempo_estimado_h'],
                'fecha_de_entrega'  => $validated['fecha_de_entrega'] ?? null,
            ]);

            // 2) Historial si cambió el estado
            if ($estadoAnterior !== $nuevoEstado) {
                TareaEstadoHistorial::create([
                    'id'                 => (string) Str::uuid(),
                    'tarea_id'           => $tarea->id,
                    'cambiado_por'       => Auth::id() ?? $validated['usuario_id'],
                    'estado_id_anterior' => $estadoAnterior,
                    'estado_id_nuevo'    => $nuevoEstado,
                    'observacion'        => 'Cambio de estado en edición',
                ]);
            }

            // 3) Re-activar SOLO desde edición
            if ($solicitaReactivar || $reaperturaPorEstado) {
                $tarea->forceFill([
                    'finalizada_at'  => null,
                    'finalizada_por' => null,
                ])->save();

                TareaEstadoHistorial::create([
                    'id'                 => (string) Str::uuid(),
                    'tarea_id'           => $tarea->id,
                    'cambiado_por'       => Auth::id() ?? $validated['usuario_id'],
                    'estado_id_anterior' => $estadoAnterior,
                    'estado_id_nuevo'    => $nuevoEstado,
                    'observacion'        => 'Reapertura de tarea desde edición',
                ]);
            }

            // 4) Si no se reactivó y el nuevo estado es final, sellar finalización
            if (!$solicitaReactivar && !$reaperturaPorEstado && in_array($nuevoEstado, EstadoTarea::finalIds(), true)) {
                $tarea->forceFill([
                    'finalizada_at'  => $tarea->finalizada_at ?: now(),
                    'finalizada_por' => $tarea->finalizada_por ?: (Auth::id() ?? $validated['usuario_id']),
                ])->save();
            }
        });

        // Reescritura/recursos
        $descripcionNueva = $this->syncRecursosDesdeDescripcion($tarea, $request->session()->getId());
        if ($descripcionNueva !== $tarea->descripcion) {
            $tarea->update(['descripcion' => $descripcionNueva]);
        }



                $tarea->refresh()->loadMissing('area', 'columna.tablero.cliente');

$nuevoUsuarioId = (int) $tarea->usuario_id;
if ($nuevoUsuarioId !== $usuarioAnteriorId) {
    $nuevoUser = User::find($nuevoUsuarioId);
    if ($nuevoUser) {
        $nuevoUser->notify(new NotificacionAsignacionTarea($tarea));
    }
    // (Opcional) notificar al anterior que fue desasignado con otra notif
}

if ($solicitaReactivar || $reaperturaPorEstado) {
    $asignado = User::find($tarea->usuario_id);
    if ($asignado) {
        $asignado->notify(new NotificacionAsignacionTarea($tarea));
    }
}




        return redirect()->route('configuracion.servicios.tableros.show', [
            'cliente'  => $cliente->id,
            'servicio' => $servicio->id,
            'tablero'  => $tablero->id,
        ])->with('success', "Tarea «{$tarea->titulo}» actualizada correctamente.");


    }
public function destroy(
    Request $request,
    Cliente $cliente,
    Servicio $servicio,
    TableroServicio $tablero,
    ColumnaTableroServicio $columna,
    TareaServicio $tarea
) {
    // 1) Validación de jerarquía (mantener para evitar acceso a objetos fuera del contexto)
    abort_unless(optional($tarea->columna)->id === $columna->id, 404);
    abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
    abort_unless($tablero->servicio_id === $servicio->id, 404);
    abort_unless($servicio->cliente_id === $cliente->id, 404);

    // 2) Borrado seguro (sin policy)
    DB::transaction(function () use ($tarea) {
        // Cargar relaciones si no están cargadas
        $tarea->loadMissing('recursos', 'timeLogs');

        // Borrar archivos físicos asociados a recursos
        foreach ($tarea->recursos as $recurso) {
            if ($recurso->path && Storage::disk('public')->exists($recurso->path)) {
                Storage::disk('public')->delete($recurso->path);
            }
        }
        // Borrar carpeta completa de la tarea
        Storage::disk('public')->deleteDirectory("tareas/{$tarea->id}");

        // Eliminar registros relacionados (si no usas FK en cascada, deja estas líneas)
        if (method_exists($tarea, 'recursos'))    $tarea->recursos()->delete();
        if (method_exists($tarea, 'timeLogs'))    $tarea->timeLogs()->delete();
        if (method_exists($tarea, 'estadoHistorial')) {
            $tarea->estadoHistorial()->delete();
        } else {
            DB::table('tarea_estados_historial')->where('tarea_id', $tarea->id)->delete();
        }

        // Despachar borrado de eventos en Google para bloques y evento de tarea (si existen)
        // (El Observer 'deleted' se encargará de esto también, pero si usamos SoftDeletes, el observer 'deleted' salta. 
        //  Si usamos forceDelete, salta 'forceDeleted'. 
        //  Como en destroy() usamos delete() (soft o hard dependiendo del modelo), confiamos en el observer.
        //  AUNQUE, la transacción aquí podría complicar las cosas si el observer se ejecuta afterCommit. 
        //  Pero dejémoslo limpio para evitar doble dispatch si el observer funciona bien.)
        // dispatch(new \App\Jobs\RemoveTaskBlocksFromCalendarJob($tarea->id))->onQueue('calendar');
        // dispatch(new \App\Jobs\RemoveTaskCalendarEvent($tarea->id))->onQueue('calendar');

        // Borrar la tarea
        $tarea->delete();
    });

    return redirect()->route('configuracion.servicios.tableros.show', [
        'cliente'  => $cliente->id,
        'servicio' => $servicio->id,
        'tablero'  => $tablero->id,
    ])->with('success', "Tarea «{$tarea->titulo}» eliminada correctamente.");
}

public function updateEstadoTiempo(
        Request $request,
        Cliente $cliente,
        Servicio $servicio,
        TableroServicio $tablero,
        ColumnaTableroServicio $columna,
        TareaServicio $tarea
    ) {
        // Validación jerárquica
        abort_unless(optional($tarea->columna)->id === $columna->id, 404);
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Validación de inputs
        $validated = $request->validate([
            'estado_id'        => ['required', Rule::exists('estado_tarea', 'id')],
            'duracion_real_h'  => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'comentario_html'  => ['nullable', 'string'],
        ]);

        $nuevoEstadoId = (int) $validated['estado_id'];
        $duracionRealH = (float) ($validated['duracion_real_h'] ?? 0);
        $comentarioRaw = (string) ($validated['comentario_html'] ?? '');

        // Normaliza comentario (HTML -> texto significativo)
        $comentarioPlano = trim(
            preg_replace('/\s+/u', ' ',
                strip_tags(
                    str_ireplace(['<p><br></p>', '&nbsp;'], '', $comentarioRaw)
                )
            )
        );
        $hasComment = $comentarioPlano !== '';

        // Reglas de negocio:
        // Comentario SIEMPRE requerido.
        // - Si estado actual es Programada/Pendiente: comentario + (cambio de estado) Y (horas > 0)
        // - En otros estados: comentario + (cambio de estado O horas > 0)
        $estadoActualId   = (int) $tarea->estado_id;
        $estadoActualName = mb_strtolower((string) optional($tarea->estado)->nombre);
        $isProgramada     = in_array($estadoActualName, ['programada','pendiente'], true);
        $estadoCambia     = $estadoActualId !== $nuevoEstadoId;
        $horasOk          = $duracionRealH > 0;

        if (!$hasComment) {
            return back()->with('comment_error', 'Debes escribir un comentario para guardar la actualización.')->withInput();
        }

        if ($isProgramada) {
            if (!($estadoCambia && $horasOk)) {
                return back()->with('comment_error', 'La tarea está en "Programada". Debes cambiar el estado y asignar horas (> 0 h), además del comentario.')->withInput();
            }
        } else {
            if (!($estadoCambia || $horasOk)) {
                return back()->with('comment_error', 'Debes cambiar el estado o asignar horas (> 0 h), además del comentario.')->withInput();
            }
        }

        // Sanitiza HTML básico del comentario
        $comentarioLimpio = $this->sanitizeHtmlBasic($comentarioRaw);

        // Señales para notificación post-commit
        $dispararNotifFinalizacion = false;
        $finalizadaPorId           = null;
        $comentarioIdCreado        = null;

        DB::transaction(function () use (
            $tarea, $nuevoEstadoId, $duracionRealH, $comentarioLimpio,
            &$dispararNotifFinalizacion, &$finalizadaPorId, &$comentarioIdCreado
        ) {
            $ahoraUtc         = now('UTC');
            $userId           = auth()->id();
            $estadoAnterior   = (int) $tarea->estado_id;
            $veniaFinalizada  = !is_null($tarea->finalizada_at);

            // 1) Registrar time log si corresponde
            if ($duracionRealH > 0) {
                $segundos = (int) round($duracionRealH * 3600);
                $started  = (clone $ahoraUtc)->subSeconds($segundos);

                TareaTimeLog::create([
                    'id'         => (string) Str::uuid(),
                    'tarea_id'   => $tarea->id,
                    'usuario_id' => $userId,
                    'started_at' => $started,
                    'ended_at'   => $ahoraUtc,
                    'duracion_h' => $duracionRealH,
                    'nota'       => null,
                ]);
            }

            // 2) Cambio de estado
            if ($estadoAnterior !== $nuevoEstadoId) {
                $tarea->forceFill(['estado_id' => $nuevoEstadoId])->save();

                TareaEstadoHistorial::create([
                    'id'                 => (string) Str::uuid(),
                    'tarea_id'           => $tarea->id,
                    'cambiado_por'       => $userId,
                    'estado_id_anterior' => $estadoAnterior,
                    'estado_id_nuevo'    => $nuevoEstadoId,
                ]);
            }

            // 3) Finalización / Reapertura
            $esFinal = in_array($nuevoEstadoId, EstadoTarea::finalIds(), true);

            if ($esFinal) {
                $primeraVez = is_null($tarea->finalizada_at);

                $tarea->forceFill([
                    'finalizada_at'  => $tarea->finalizada_at ?: $ahoraUtc,
                    'finalizada_por' => $tarea->finalizada_por ?: $userId,
                ])->save();

                if ($primeraVez) {
                    $dispararNotifFinalizacion = true;
                    $finalizadaPorId = $userId;
                }
            } elseif ($veniaFinalizada) {
                $tarea->forceFill([
                    'finalizada_at'  => null,
                    'finalizada_por' => null,
                ])->save();
            }

            // 4) Crear comentario (siempre obligatorio por las reglas)
            $comentario = TareaComentario::create([
                'id'         => (string) Str::uuid(),
                'tarea_id'   => $tarea->getKey(),
                'usuario_id' => $userId,
                'comentario' => $comentarioLimpio,
            ]);

            $comentarioIdCreado = $comentario->id;
        });

        // ================== NOTIFICACIONES (post-commit) ==================
        try {
            $actorId = (int) (auth()->id() ?? 0);

            // Cargar lo necesario para las notificaciones
            $tarea->loadMissing('area', 'columna.tablero.cliente', 'historiales.autor', 'usuarios', 'estado');

            // --- A) Notificación por FINALIZACIÓN (solo si se finalizó por primera vez y no al propio actor) ---
            if ($dispararNotifFinalizacion) {
                // creador = primer autor de historiales
                $creador = $tarea->historiales
                    ->sortBy('created_at')
                    ->first()?->autor; // User|null

                if ($creador && (int) $creador->id !== (int) $finalizadaPorId) {
                    $creador->notify(new NotificacionTareaFinalizada($tarea, $finalizadaPorId));
                }
            }

            // --- B) Notificación por COMENTARIO/ACTUALIZACIÓN ---
            // Resumen plano del comentario (limpio y corto)
            $comentarioResumen = \Illuminate\Support\Str::of(
                trim(preg_replace('/\s+/u', ' ', strip_tags($comentarioRaw)))
            )->squish()->limit(180, '…');

            // Construir lista de destinatarios:
            $destinatarios = collect();

            // 1) Asignado principal
            if ($tarea->usuario_id) {
                if ($u = User::find($tarea->usuario_id)) {
                    $destinatarios->push($u);
                }
            }

            // 2) Creador desde historiales (si existe)
            $creador = $tarea->historiales->sortBy('created_at')->first()?->autor;
            if ($creador) {
                $destinatarios->push($creador);
            }

            // 3) Asignados adicionales (si hay muchos-usuarios)
            if ($tarea->relationLoaded('usuarios')) {
                $destinatarios = $destinatarios->merge($tarea->usuarios);
            } else {
                // Si no estaba cargada, podrías traerlos: $destinatarios = $destinatarios->merge($tarea->usuarios()->get());
            }

            // Limpiar duplicados/nulos y no auto-notificar al actor
            $destinatarios = $destinatarios
                ->filter()
                ->unique('id')
                ->reject(fn ($u) => (int) $u->id === $actorId);

            foreach ($destinatarios as $user) {
                $user->notify(new NotificacionComentarioTarea($tarea, (string) $comentarioResumen, $actorId));
            }
        } catch (\Throwable $e) {
            \Log::warning('[Notif] Falló notificar actualización de tarea', [
                'tarea_id'  => $tarea->id,
                'actor_id'  => auth()->id(),
                'error'     => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Actualización guardada correctamente.');
    }

    private function sanitizeHtmlBasic(string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><s><span><blockquote><pre><code>'
                 . '<ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><div>';

        $clean = strip_tags($html, $allowed);

        // elimina on* handlers
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean);

        // <a> seguro
        $clean = preg_replace_callback('/<a\s+[^>]*href=("|\')(.*?)\1[^>]*>/i', function($m) {
            $href = $m[2];
            if (!preg_match('#^(https?:|mailto:)#i', $href)) {
                $href = '#';
            }
            return '<a href="'.$href.'" target="_blank" rel="noopener nofollow">';
        }, $clean);

        // <img> seguro
        $clean = preg_replace_callback('/<img\s+[^>]*src=("|\')(.*?)\1[^>]*>/i', function($m) {
            $src = $m[2];
            if (!preg_match('#^(https?:|/storage/)#i', $src)) {
                return ''; // descarta
            }
            return '<img src="'.$src.'" alt="">';
        }, $clean);

        return $clean;
    }

}