<?php

namespace App\Jobs;

use App\Models\{TareaServicio, UserGoogleAccount, TaskCalendarEvent};
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
        $tarea = TareaServicio::find($this->tareaId);
        if (!$tarea) return;

        $acc = UserGoogleAccount::where('user_id', $this->userId)->first();
        if (!$acc) return;

        $start = $tarea->fecha_de_entrega?->copy()->subHour() ?? now()->addMinutes(15);
        $end   = $start->copy()->addHour();

        $summary = sprintf(
            '[%s] %s',
            optional($tarea->columna->tablero->cliente)->nombre ?? 'Cliente',
            $tarea->titulo
        );

        $url = route('configuracion.servicios.tableros.show', [
            'cliente' => $tarea->columna->tablero->cliente_id ?? '',
            'servicio' => $tarea->columna->tablero->servicio_id ?? '',
            'tablero' => $tarea->columna->tablero->id ?? '',
        ]);

        $desc = ($tarea->descripcion ? strip_tags($tarea->descripcion) . "\n\n" : '')
            . "Enlace al tablero: {$url}";

        $eventId = $svc->createEvent($acc, [
            'summary'     => $summary,
            'description' => $desc,
            'start'       => $start,
            'end'         => $end,
        ]);

        TaskCalendarEvent::updateOrCreate(
            ['tarea_id' => $tarea->id, 'user_id' => $this->userId],
            ['id' => (string)Str::uuid(), 'calendar_id' => $acc->calendar_id ?: 'primary', 'google_event_id' => $eventId]
        );
    }
}
