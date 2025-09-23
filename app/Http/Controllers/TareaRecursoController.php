<?php

namespace App\Http\Controllers;

use App\Models\TareaServicio;
use App\Models\TareaRecurso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TareaRecursoController extends Controller
{
    /**
     * Subida genérica para "drafts" (cuando NO hay tarea aún).
     * Mantén esta acción si ya la usas en otros flujos.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required', 'file', 'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'
            ],
        ]);

        $file      = $request->file('file');
        $sessionId = $request->session()->getId();

        $dir  = "tareas/draft/{$sessionId}";
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($dir, $name, 'public');

        return response()->json([
            'url'        => Storage::disk('public')->url($path),
            'path'       => $path,
            'mime'       => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'title'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    /**
     * Subida "smart" para Quill con tarea opcional.
     * - Si viene una tarea → persiste el recurso ligado a esa tarea (tipo image).
     * - Si NO viene tarea → guarda como draft (misma lógica del store(), pero para imágenes).
     *
     * Ruta recomendada:
     *   POST /tareas/{tarea?}/quill/upload  -> name: quill.upload
     */
    public function quillUpload(Request $request, ?TareaServicio $tarea = null): JsonResponse
    {
        // Quill solo inserta imágenes (si luego quieres permitir otros tipos, amplía aquí).
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'], // 5MB
        ]);

        $file = $request->file('file');

        if ($tarea) {
            // === Caso con tarea: persistimos como recurso de la tarea ===
            $dir      = 'tareas/'.date('Y/m');
            $filename = Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $path     = $file->storeAs($dir, $filename, 'public');
            $url      = Storage::disk('public')->url($path);

            // Calcula el siguiente orden (si usas 'orden')
            $orden = (int) TareaRecurso::where('tarea_id', $tarea->getKey())->max('orden');
            $orden++;

            // Ajusta los campos a tu esquema real (ruta/url/titulo/tipo/orden)
            TareaRecurso::create([
                'id'       => (string) Str::uuid(),
                'tarea_id' => $tarea->getKey(),
                'tipo'     => 'image',
                'titulo'   => $file->getClientOriginalName(),
                'ruta'     => $path,          // guarda el path relativo
                // 'url'   => $url,            // usa 'url' en vez de 'ruta' si tu esquema lo requiere
                'orden'    => $orden,
            ]);

            return response()->json(['url' => $url, 'path' => $path], 201);
        }

        // === Caso sin tarea (CREATE): guarda como draft (similar a store()) ===
        $sessionId = $request->session()->getId();
        $dir  = "tareas/draft/{$sessionId}";
        $name = Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs($dir, $name, 'public');
        $url  = Storage::disk('public')->url($path);

        return response()->json(['url' => $url, 'path' => $path], 201);
    }

    /**
     * Forzar descarga de un recurso adjunto de una tarea.
     */
    public function download(TareaServicio $tarea, TareaRecurso $recurso)
    {
        // Seguridad básica: que el recurso pertenezca a la tarea
        if ((string) $recurso->tarea_id !== (string) $tarea->id) {
            abort(404);
        }

        $raw = (string) ($recurso->url ?? $recurso->ruta ?? '');
        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            // Enlace externo: redirige (no se puede forzar attachment externo)
            return redirect()->away($raw);
        }

        // Normaliza separadores y resuelve ruta relativa para disco public
        $val = str_replace('\\', '/', $raw);

        $publicRoot = str_replace('\\', '/', public_path());
        if (Str::startsWith($val, $publicRoot)) {
            $val = Str::after($val, $publicRoot);
        }

        $val = '/'.ltrim($val, '/');
        if (Str::startsWith($val, '/storage/')) {
            $relative = ltrim(Str::after($val, '/storage/'), '/');
        } elseif (Str::startsWith($val, ['/tareas/', 'tareas/'])) {
            $relative = ltrim(Str::after($val, '/'), '/');
        } else {
            $relative = ltrim($val, '/');
        }

        $ext  = pathinfo($relative, PATHINFO_EXTENSION) ?: 'bin';
        $base = $recurso->titulo
            ? Str::slug($recurso->titulo)
            : (pathinfo($relative, PATHINFO_FILENAME) ?: 'archivo');

        $downloadName = $base.'.'.$ext;

        // 1) Disco 'public'
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->download($relative, $downloadName);
        }

        // 2) Fallback a public/storage
        $absPublicStorage = public_path('storage/'.$relative);
        if (is_file($absPublicStorage)) {
            return response()->download($absPublicStorage, $downloadName);
        }

        abort(404, 'Archivo no encontrado');
    }
}