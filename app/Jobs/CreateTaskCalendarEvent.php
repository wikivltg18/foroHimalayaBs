<?php

namespace App\Jobs;

use App\Models\{TareaServicio, UserGoogleAccount, TaskCalendarEvent, TaskCalendarBlockEvent};
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CreateTaskCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tareaId, public int $userId) {}

    public function handle(GoogleCalendarService $svc): void
    {
        $tarea = TareaServicio::with(['bloques', 'columna.tablero'])->find($this->tareaId);
        if (!$tarea) return;

        $acc = UserGoogleAccount::where('user_id', $this->userId)->first();
        if (!$acc) return;

        // Usar el calendario de la tarea si está definido, sino el del usuario
        $calendarId = $tarea->google_calendar_id ?? ($acc->calendar_id ?: 'primary');

        // ============ ELIMINAR EVENTOS ANTIGUOS ANTES DE CREAR NUEVOS ============
        // Esto garantiza que al actualizar no se dupliquen eventos
        
        // 1. Eliminar eventos de bloques antiguos
        $oldBlockEvents = TaskCalendarBlockEvent::whereHas('bloque', function($q) use ($tarea) {
            $q->where('tarea_id', $tarea->id);
        })->where('user_id', $this->userId)->get();
        
        foreach ($oldBlockEvents as $blockEvent) {
            try {
                $svc->deleteEvent($acc, $blockEvent->google_event_id, $blockEvent->calendar_id);
            } catch (\Exception $e) {
                \Log::warning("No se pudo eliminar evento de bloque: {$blockEvent->google_event_id}");
            }
            $blockEvent->delete();
        }

        // 2. Eliminar evento genérico si existe
        $oldEvent = TaskCalendarEvent::where('tarea_id', $tarea->id)
            ->where('user_id', $this->userId)
            ->first();
            
        if ($oldEvent) {
            try {
                $svc->deleteEvent($acc, $oldEvent->google_event_id, $oldEvent->calendar_id);
            } catch (\Exception $e) {
                \Log::warning("No se pudo eliminar evento genérico: {$oldEvent->google_event_id}");
            }
            $oldEvent->delete();
        }
        // ============ /ELIMINAR EVENTOS ANTIGUOS ============

        // Generar URL completa de la tarea
        $url = route('configuracion.servicios.tableros.show', [
            'cliente' => $tarea->columna->tablero->cliente_id ?? '',
            'servicio' => $tarea->columna->tablero->servicio_id ?? '',
            'tablero' => $tarea->columna->tablero->id ?? '',
        ]);

        $summary = sprintf(
            '[%s] %s',
            optional($tarea->columna->tablero->cliente)->nombre ?? 'Cliente',
            $tarea->titulo
        );

        // Descripción con enlace clicable y contenido de la tarea
        $desc = ($tarea->descripcion ? strip_tags($tarea->descripcion) . "\n\n" : '')
            . "Ver tarea: {$url}";

        // Obtener bloques de trabajo para este usuario
        $bloques = $tarea->bloques()->where('user_id', $this->userId)->get();

        if ($bloques->isEmpty()) {
            // Si no hay bloques, usar fecha de entrega o crear evento genérico
            $start = $tarea->fecha_de_entrega?->copy()->subHour() ?? now()->addMinutes(15);
            $end   = $start->copy()->addHours((float) $tarea->tiempo_estimado_h ?: 1);

            $eventId = $svc->createEvent($acc, [
                'summary'     => $summary,
                'description' => $desc,
                'start'       => $start,
                'end'         => $end,
            ], $calendarId);

            TaskCalendarEvent::create([
                'id' => (string)Str::uuid(),
                'tarea_id' => $tarea->id,
                'user_id' => $this->userId,
                'calendar_id' => $calendarId,
                'google_event_id' => $eventId,
            ]);
        } else {
            // Crear un evento por cada bloque de trabajo
            foreach ($bloques as $bloque) {
                $blockSummary = $summary . " (Bloque {$bloque->orden})";
                
                $eventId = $svc->createEvent($acc, [
                    'summary'     => $blockSummary,
                    'description' => $desc,
                    'start'       => $bloque->inicio,
                    'end'         => $bloque->fin,
                ], $calendarId);

                // Asociar evento con el bloque
                TaskCalendarBlockEvent::create([
                    'id' => (string)Str::uuid(),
                    'tarea_bloque_id' => $bloque->id,
                    'user_id' => $this->userId,
                    'calendar_id' => $calendarId,
                    'google_event_id' => $eventId,
                ]);
            }
        }
    }
}
