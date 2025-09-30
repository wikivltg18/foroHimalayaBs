<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TareaServicio;
use App\Models\TareaComentario;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NotificacionComentarioTarea;
use App\Models\User;

class TareaComentarioController extends Controller
{
    public function store(Request $request, TareaServicio $tarea)
    {
        return back()->with('error', 'Para comentar usa el botón "Guardar" del formulario unificado (cambia el estado a uno distinto de Programada).');
    }

    public function destroy(TareaServicio $tarea, TareaComentario $comentario)
    {
        // Asegura que el comentario pertenece a la tarea
        if ($comentario->tarea_id !== $tarea->getKey()) {
            abort(404);
        }

        // Checks sin policies:
        $userId = Auth::id();
        $esAutorComentario = $comentario->usuario_id === $userId;
        $esPropietarioTarea = (int) $tarea->usuario_id === (int) $userId;
        $esAsignadoEnTarea  = $tarea->usuarios()->where('users.id', $userId)->exists();

        if (!($esAutorComentario || $esPropietarioTarea || $esAsignadoEnTarea)) {
            abort(403);
        }

        $comentario->delete();

        return back()->with('comment_success', 'Comentario eliminado.');
    }

    private function sanitizeHtml(string $html): string
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