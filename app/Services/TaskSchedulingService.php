<?php

namespace App\Services;

use App\Models\User;
use App\Models\TareaServicio;
use App\Models\TareaBloque;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TaskSchedulingService
{
    /**
     * Horarios de trabajo
     * Formato HH:mm
     */
    private const WORK_BLOCKS = [
        ['start' => '08:00', 'end' => '12:15'],
        ['start' => '13:45', 'end' => '18:00'],
    ];

    /**
     * Obtiene el siguiente día laboral a partir de una fecha dada.
     * Salta fines de semana.
     */
    /**
     * Obtiene el siguiente día laboral a partir de una fecha dada.
     * Salta fines de semana.
     */
    private function getNextWorkDay(Carbon $date): Carbon
    {
        // $date debe venir en la timezone local de trabajo
        $next = $date->copy()->addDay();

        // Si es sábado (6) -> saltar a lunes (+2 días)
        if ($next->dayOfWeek === Carbon::SATURDAY) {
            $next->addDays(2);
        }
        // Si es domingo (0) -> saltar a lunes (+1 día)
        elseif ($next->dayOfWeek === Carbon::SUNDAY) {
            $next->addDay();
        }
        
        // Reiniciar a la hora de inicio del primer bloque del día
        $firstBlockStart = explode(':', self::WORK_BLOCKS[0]['start']);
        $next->setTime((int)$firstBlockStart[0], (int)$firstBlockStart[1], 0);

        return $next;
    }

    /**
     * Método principal para crear bloques programados divididos por franjas horarias y días.
     * Adapta la lógica de "createEvents" solicitada.
     */
    public function createScheduledBlocks(
        TareaServicio $tarea,
        int $userId,
        Carbon $requestedStart
    ): array {
        // Duración total en minutos
        $durationMinutes = (float) $tarea->tiempo_estimado_h * 60;
        $remaining = $durationMinutes;
        
        // Trabajar en la zona horaria DEL NEGOCIO (display_timezone)
        // para que las 08:00 sean las 08:00 locales.
        $displayTz = config('app.display_timezone', 'America/Bogota');
        $currentDate = $requestedStart->copy()->setTimezone($displayTz);
        
        // Asegurarse de que currentDate no empiece antes del primer bloque si es un día laboral, 
        // o ajustar al siguiente día laboral si es fin de semana/fuera de hora.
        if ($currentDate->isWeekend()) {
            $currentDate = $this->getNextWorkDay($currentDate->subDay()); 
        }

        $blocksCreated = [];
        $maxOrder = TareaBloque::where('tarea_id', $tarea->id)->max('orden') ?? 0;

        // Limite de seguridad
        $safetyCounter = 0;
        $safetyLimit = 365; 

        while ($remaining > 0 && $safetyCounter < $safetyLimit) {
            foreach (self::WORK_BLOCKS as $block) {
                if ($remaining <= 0) break;

                // Definir inicio y fin del bloque para el día actual (EN HORARIO LOCAL)
                $blockStart = $currentDate->copy()->setTimeFromTimeString($block['start']);
                $blockEnd   = $currentDate->copy()->setTimeFromTimeString($block['end']);

                // Si la fecha actual ya pasó el fin de este bloque, continuamos al siguiente
                if ($currentDate->gt($blockEnd)) {
                    continue;
                }

                // Ajustar el inicio si la fecha actual está dentro del bloque
                if ($currentDate->gt($blockStart)) {
                    $effectiveStart = $currentDate->copy();
                } else {
                    $effectiveStart = $blockStart;
                }

                // Calcular duración disponible en este bloque
                $availableMinutes = $effectiveStart->diffInMinutes($blockEnd, false);

                if ($availableMinutes <= 0) continue;

                // Definir cuánto de la tarea cabe en este bloque
                if ($remaining <= $availableMinutes) {
                    // La tarea termina en este bloque
                    $finalEnd = $effectiveStart->copy()->addMinutes($remaining);
                    
                    $blocksCreated[] = $this->persistBlock($tarea, $userId, $effectiveStart, $finalEnd, ++$maxOrder);
                    
                    $remaining = 0;
                } else {
                    // La tarea ocupa todo el resto del bloque y continúa
                    $blocksCreated[] = $this->persistBlock($tarea, $userId, $effectiveStart, $blockEnd, ++$maxOrder);
                    
                    $remaining -= $availableMinutes;
                }
            }

            if ($remaining > 0) {
                // Pasar al siguiente día laboral
                $currentDate = $this->getNextWorkDay($currentDate);
                $safetyCounter++;
            }
        }

        return $blocksCreated;
    }

    /**
     * Guarda el bloque en la base de datos.
     */
    private function persistBlock(TareaServicio $tarea, int $userId, Carbon $start, Carbon $end, int $order): TareaBloque
    {
        // Convertir de vuelta a UTC (timezoned app) para almacenamiento consistente
        $appTz = config('app.timezone', 'UTC');

        return TareaBloque::create([
            'id'           => (string) Str::uuid(),
            'tarea_id'     => $tarea->id,
            'user_id'      => $userId,
            'scheduled_by' => auth()->id(),
            'inicio'       => $start->copy()->setTimezone($appTz),
            'fin'          => $end->copy()->setTimezone($appTz),
            'orden'        => $order,
        ]);
    }
}
