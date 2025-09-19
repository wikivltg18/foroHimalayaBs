<?php

namespace App\Http\Controllers;

use App\Models\TareaServicio;
use Illuminate\Support\Str;
use App\Models\TareaRecurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TareaRecursoController extends Controller
{
    public function index() { /* ... */ }
    public function create() { /* ... */ }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
        ]);

        $file      = $request->file('file');
        $sessionId = $request->session()->getId();

        $dir  = "tareas/draft/{$sessionId}";
        $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($dir, $name, 'public');

        return response()->json([
            'url'        => Storage::disk('public')->url($path),
            'path'       => $path,
            'mime'       => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'title'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    public function show(TareaRecurso $tareaRecurso) { /* ... */ }
    public function edit(TareaRecurso $tareaRecurso) { /* ... */ }
    public function update(Request $request, TareaRecurso $tareaRecurso) { /* ... */ }
    public function destroy(TareaRecurso $tareaRecurso) { /* ... */ }

    public function download(TareaServicio $tarea, TareaRecurso $recurso)
    {
        // Seguridad básica: que el recurso pertenezca a la tarea
        if ((int) $recurso->tarea_id !== (int) $tarea->id) {
            abort(404);
        }

        // Si es un enlace externo, redirige (no podemos forzar attachment en dominio externo)
        $raw = (string)($recurso->url ?? $recurso->ruta ?? '');
        if (Str::startsWith($raw, ['http://','https://','//'])) {
            return redirect()->away($raw);
        }

        // Normaliza separadores Windows -> URL
        $val = str_replace('\\', '/', $raw);

        // Si es un path absoluto dentro de public/, recortar el prefijo
        $publicRoot = str_replace('\\', '/', public_path());
        if (Str::startsWith($val, $publicRoot)) {
            $val = Str::after($val, $publicRoot);
        }

        // Asegura prefijo único
        $val = '/'.ltrim($val, '/');

        // Si ya viene como /storage/...
        if (Str::startsWith($val, '/storage/')) {
            $relative = ltrim(Str::after($val, '/storage/'), '/');
        }
        // Si viene como /tareas/... o tareas/...
        elseif (Str::startsWith($val, ['/tareas/', 'tareas/'])) {
            $relative = ltrim(Str::after($val, '/'), '/'); // quita primer slash si existe
        }
        // Cualquier otro caso, lo tratamos como relativo al disco public
        else {
            $relative = ltrim($val, '/');
        }

        // Nombre de descarga
        $ext = pathinfo($relative, PATHINFO_EXTENSION) ?: 'bin';
        $base = $recurso->titulo
            ? Str::slug($recurso->titulo)
            : (pathinfo($relative, PATHINFO_FILENAME) ?: 'archivo');
        $downloadName = $base . '.' . $ext;

        // 1) Intenta por Storage "public" (storage/app/public)
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->download($relative, $downloadName);
        }

        // 2) Fallback: archivo físico en public/storage (por si los subiste directo ahí)
        $absPublicStorage = public_path('storage/' . $relative);
        if (is_file($absPublicStorage)) {
            return response()->download($absPublicStorage, $downloadName);
        }

        // 3) Último recurso: si la vista genera una URL servible, podríamos redirigir
        // return redirect(url('/storage/'.$relative, [], false));

        abort(404, 'Archivo no encontrado');
    }
}