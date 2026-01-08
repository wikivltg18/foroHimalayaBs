<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TareaServicio;
use App\Models\TareaBloque;
use App\Services\AgendaScheduler;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    public function index()
    {
        return view('agenda.index');
    }

    public function resources()
    {
        return User::selectRaw('id as id, name as title')->orderBy('name')->get();
    }

    public function events(Request $r)
    {
        $from = Carbon::parse($r->query('from'));
        $to   = Carbon::parse($r->query('to'));

        // 1. Bloques de trabajo programados
        $bloques = TareaBloque::with('tarea')
            ->where(function($q) use ($from,$to) {
                $q->whereBetween('inicio', [$from, $to])
                  ->orWhereBetween('fin',   [$from, $to])
                  ->orWhere(function($qq) use ($from,$to){
                      $qq->where('inicio','<=',$from)->where('fin','>=',$to);
                  });
            })
            ->get()
            ->map(function($b){
                return [
                    'id'         => $b->id,
                    'resourceId' => $b->user_id,
                    'start'      => $b->inicio->toIso8601String(),
                    'end'        => $b->fin->toIso8601String(),
                    'title'      => $b->tarea->titulo,
                    'extendedProps' => [
                        'type'     => 'block',
                        'tarea_id' => $b->tarea_id,
                        'orden'    => $b->orden,
                    ],
                ];
            });

        // 2. Tareas por fecha de entrega (Hitos/Deadlines)
        // Solo tareas con usuario asignado y fecha de entrega en el rango
        $entregas = TareaServicio::with('usuario')
            ->whereNotNull('usuario_id')
            ->whereNotNull('fecha_de_entrega')
            ->whereBetween('fecha_de_entrega', [$from, $to])
            ->get()
            ->map(function($t){
                // Representamos la entrega como un evento de 1 hora terminando en la hora de entrega, 
                // o un evento de todo el día si prefieres. 
                // Aquí: 1 hora antes de la fecha_de_entrega hasta fecha_de_entrega.
                $end   = $t->fecha_de_entrega;
                $start = $end->copy()->subHour(); 

                return [
                    'id'         => 'delivery_' . $t->id,
                    'resourceId' => $t->usuario_id,
                    'start'      => $start->toIso8601String(),
                    'end'        => $end->toIso8601String(),
                    'title'      => '📅 Entrega: ' . $t->titulo,
                    'backgroundColor' => '#dc3545', // Rojo para diferenciar
                    'borderColor'     => '#dc3545',
                    'extendedProps' => [
                        'type'     => 'delivery',
                        'tarea_id' => $t->id,
                    ],
                ];
            });

        return response()->json($bloques->merge($entregas));
    }

    public function schedule(Request $r, AgendaScheduler $sched)
    {
        $r->validate([
            'tarea_id' => 'required|uuid|exists:tarea_servicios,id',
            'user_id'  => 'required|integer|exists:users,id',
            'start'    => 'required|date',
        ]);

        $tarea = TareaServicio::findOrFail($r->tarea_id);

        // Authorization
        if (!\Gate::allows('schedule-task', $tarea)) {
            return response()->json(['message' => 'No autorizado para agendar esta tarea'], 403);
        }

        try {
            $start = Carbon::parse($r->start);
            $sched->schedule($tarea, (int)$r->user_id, $start, auth()->id());
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \Log::error('Agenda schedule error: ' . $e->getMessage());
            return response()->json(['message' => 'Error al agendar la tarea', 'detail' => $e->getMessage()], 500);
        }
    }

    public function moveBlock(Request $r, AgendaScheduler $sched)
    {
        $r->validate([
            'block_id'  => 'required|uuid|exists:tarea_bloques,id',
            'new_start' => 'required|date',
        ]);

        $block = TareaBloque::findOrFail($r->block_id);
        $tarea = TareaServicio::findOrFail($block->tarea_id);

        if (!\Gate::allows('schedule-task', $tarea)) {
            return response()->json(['message' => 'No autorizado para mover este bloque'], 403);
        }

        try {
            // Estrategia simple: reprograma la tarea desde el nuevo inicio para ese user
            $sched->schedule($tarea, (int)$block->user_id, Carbon::parse($r->new_start), auth()->id());
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \Log::error('Agenda moveBlock error: ' . $e->getMessage());
            return response()->json(['message' => 'Error al mover el bloque', 'detail' => $e->getMessage()], 500);
        }
    }

    public function unschedule(Request $r, AgendaScheduler $sched)
    {
        $r->validate([
            'tarea_id' => 'required|uuid|exists:tarea_servicios,id',
            'user_id'  => 'required|integer|exists:users,id',
        ]);

        $tarea = TareaServicio::findOrFail($r->tarea_id);
        if (!\Gate::allows('schedule-task', $tarea)) {
            return response()->json(['message' => 'No autorizado para desagendar esta tarea'], 403);
        }

        try {
            $sched->unschedule($r->tarea_id, (int)$r->user_id);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \Log::error('Agenda unschedule error: ' . $e->getMessage());
            return response()->json(['message' => 'Error al desagendar la tarea', 'detail' => $e->getMessage()], 500);
        }
    }

    public function availableTasks(Request $r)
    {
        // Obtener tareas sin programar o del área/usuario actual
        $query = TareaServicio::with(['columna.tablero.cliente', 'area', 'usuario'])
            ->whereNull('archivada')
            ->whereNull('finalizada_at');

        // Opcional: filtrar por búsqueda
        if ($r->filled('search')) {
            $search = $r->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Opcional: filtrar por área
        if ($r->filled('area_id')) {
            $query->where('area_id', $r->area_id);
        }

        $tareas = $query->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'titulo' => $t->titulo,
                    'area' => $t->area?->nombre,
                    'usuario' => $t->usuario?->name,
                    'cliente' => optional($t->columna?->tablero?->cliente)->nombre,
                    'tiempo_estimado_h' => $t->tiempo_estimado_h,
                    'programada' => !is_null($t->programada_inicio),
                ];
            });

        return response()->json($tareas);
    }
}