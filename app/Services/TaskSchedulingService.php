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
     */
    private const BUSINESS_HOURS = [
        ['start' => '08:00', 'end' => '12:15'],
        ['start' => '13:45', 'end' => '18:00'],
    ];

    /**
     * Días laborales (1 = Lunes, 5 = Viernes)
     */
    private const WORK_DAYS = [1, 2, 3, 4, 5];

    /**
     * Crea un bloque de trabajo para una tarea
     */
    public function createTaskBlock(TareaServicio $tarea, int $userId, Carbon $startTime): TareaBloque
    {
        // Validar que está en horario laboral
        if (!$this->isWithinBusinessHours($startTime)) {
            throw new \InvalidArgumentException('El horario de inicio debe estar dentro de las franjas laborales.');
        }

        // Calcular hora de fin basándose en tiempo estimado
        $durationHours = (float) $tarea->tiempo_estimado_h;
        $endTime = $startTime->copy()->addHours($durationHours);

        // Verificar conflictos
        if ($this->hasConflict($userId, $startTime, $endTime)) {
            throw new \InvalidArgumentException('Ya existe un bloque programado en este horario.');
        }

        // Determinar el orden (siguiente bloque disponible para esta tarea)
        $maxOrder = TareaBloque::where('tarea_id', $tarea->id)->max('orden') ?? 0;

        return TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $userId,
            'scheduled_by' => auth()->id(),
            'inicio' => $startTime,
            'fin' => $endTime,
            'orden' => $maxOrder + 1,
        ]);
    }

    /**
     * Actualiza un bloque de trabajo existente
     */
    public function updateTaskBlock(TareaBloque $bloque, Carbon $newStartTime): TareaBloque
    {
        if (!$this->isWithinBusinessHours($newStartTime)) {
            throw new \InvalidArgumentException('El horario de inicio debe estar dentro de las franjas laborales.');
        }

        $tarea = $bloque->tarea;
        $durationHours = (float) $tarea->tiempo_estimado_h;
        $newEndTime = $newStartTime->copy()->addHours($durationHours);

        // Verificar conflictos (excluyendo el bloque actual)
        if ($this->hasConflict($bloque->user_id, $newStartTime, $newEndTime, $bloque->id)) {
            throw new \InvalidArgumentException('Ya existe un bloque programado en este horario.');
        }

        $bloque->update([
            'inicio' => $newStartTime,
            'fin' => $newEndTime,
        ]);

        return $bloque->fresh();
    }

    /**
     * Elimina bloques de trabajo de una tarea
     */
    public function deleteTaskBlocks(TareaServicio $tarea, ?int $userId = null): int
    {
        $query = TareaBloque::where('tarea_id', $tarea->id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->delete();
    }

    /**
     * Verifica si una fecha/hora está dentro de las franjas laborales
     */
    public function isWithinBusinessHours(Carbon $datetime): bool
    {
        // Verificar que sea día laboral
        if (!in_array($datetime->dayOfWeek, self::WORK_DAYS)) {
            return false;
        }

        $time = $datetime->format('H:i');

        foreach (self::BUSINESS_HOURS as $slot) {
            if ($time >= $slot['start'] && $time < $slot['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene las franjas horarias laborales de un día
     */
    public function getBusinessHoursSlots(Carbon $day): array
    {
        if (!in_array($day->dayOfWeek, self::WORK_DAYS)) {
            return [];
        }

        return array_map(function ($slot) use ($day) {
            return [
                'start' => $day->copy()->setTimeFromTimeString($slot['start']),
                'end' => $day->copy()->setTimeFromTimeString($slot['end']),
            ];
        }, self::BUSINESS_HOURS);
    }

    /**
     * Verifica si existe un conflicto de horario
     */
    public function hasConflict(int $userId, Carbon $start, Carbon $end, ?string $excludeBlockId = null): bool
    {
        $query = TareaBloque::where('user_id', $userId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('inicio', [$start, $end])
                    ->orWhereBetween('fin', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('inicio', '<=', $start)
                            ->where('fin', '>=', $end);
                    });
            });

        if ($excludeBlockId) {
            $query->where('id', '!=', $excludeBlockId);
        }

        return $query->exists();
    }

    /**
     * Divide un bloque largo en múltiples bloques respetando franjas laborales
     * (Útil para tareas que requieren más horas de las disponibles en un día)
     */
    public function splitIntoBusinessHours(Carbon $start, float $hours): array
    {
        $blocks = [];
        $remainingHours = $hours;
        $currentStart = $start->copy();

        while ($remainingHours > 0) {
            // Encontrar la siguiente franja disponible
            $slot = $this->getNextAvailableSlot($currentStart);
            
            if (!$slot) {
                throw new \RuntimeException('No se pudo encontrar franja laboral disponible.');
            }

            $slotDuration = $slot['start']->diffInHours($slot['end'], true);
            $blockDuration = min($remainingHours, $slotDuration);

            $blocks[] = [
                'start' => $slot['start'],
                'end' => $slot['start']->copy()->addHours($blockDuration),
            ];

            $remainingHours -= $blockDuration;
            $currentStart = $slot['end']->copy()->addDay()->setTimeFromTimeString(self::BUSINESS_HOURS[0]['start']);
        }

        return $blocks;
    }

    /**
     * Encuentra la siguiente franja disponible a partir de una fecha/hora
     */
    private function getNextAvailableSlot(Carbon $datetime): ?array
    {
        $maxAttempts = 14; // 2 semanas
        $attempts = 0;
        $current = $datetime->copy();

        while ($attempts < $maxAttempts) {
            $slots = $this->getBusinessHoursSlots($current);

            foreach ($slots as $slot) {
                if ($slot['start'] >= $datetime) {
                    return $slot;
                }
            }

            $current->addDay()->setTimeFromTimeString(self::BUSINESS_HOURS[0]['start']);
            $attempts++;
        }

        return null;
    }

    /**
     * Ajusta una fecha/hora al siguiente horario laboral disponible
     * Si está fuera de horario, mueve al inicio de la siguiente franja
     * Si es fin de semana, mueve al lunes
     */
    public function adjustToBusinessHours(Carbon $datetime): Carbon
    {
        $adjusted = $datetime->copy();

        // VALIDACIÓN: No permitir fechas pasadas
        if ($adjusted->isPast()) {
            throw new \InvalidArgumentException('No se puede programar en fechas pasadas. Selecciona una fecha futura.');
        }

        // Si es fin de semana, mover al lunes
        while (!in_array($adjusted->dayOfWeek, self::WORK_DAYS)) {
            $adjusted->addDay()->setTimeFromTimeString(self::BUSINESS_HOURS[0]['start']);
        }

        // Si ya está en horario laboral, retornar
        if ($this->isWithinBusinessHours($adjusted)) {
            return $adjusted;
        }

        $time = $adjusted->format('H:i');

        // Si es antes del inicio del día laboral
        if ($time < self::BUSINESS_HOURS[0]['start']) {
            return $adjusted->setTimeFromTimeString(self::BUSINESS_HOURS[0]['start']);
        }

        // Si está en el almuerzo (entre 12:15 y 13:45)
        if ($time >= self::BUSINESS_HOURS[0]['end'] && $time < self::BUSINESS_HOURS[1]['start']) {
            return $adjusted->setTimeFromTimeString(self::BUSINESS_HOURS[1]['start']);
        }

        // Si es después del fin del día laboral, mover al siguiente día laboral
        if ($time >= self::BUSINESS_HOURS[1]['end']) {
            $adjusted->addDay();
            // Verificar si el siguiente día es laboral
            while (!in_array($adjusted->dayOfWeek, self::WORK_DAYS)) {
                $adjusted->addDay();
            }
            return $adjusted->setTimeFromTimeString(self::BUSINESS_HOURS[0]['start']);
        }

        return $adjusted;
    }

    /**
     * Verifica si un rango de tiempo cruza el almuerzo (12:15-13:45)
     */
    public function crossesLunchBreak(Carbon $start, Carbon $end): bool
    {
        // Solo verificar si es el mismo día
        if (!$start->isSameDay($end)) {
            return false;
        }

        $lunchStart = $start->copy()->setTimeFromTimeString(self::BUSINESS_HOURS[0]['end']);
        $lunchEnd = $start->copy()->setTimeFromTimeString(self::BUSINESS_HOURS[1]['start']);

        // Cruza si empieza antes del almuerzo y termina después de que empieza
        return $start->lt($lunchStart) && $end->gt($lunchStart);
    }

    /**
     * Divide una tarea que cruza el almuerzo en múltiples bloques
     * Ejemplo: 11:15-13:15 (2h) → [11:15-12:15 (1h), 13:45-14:45 (1h)]
     */
    public function splitAcrossLunchBreak(
        TareaServicio $tarea,
        int $userId,
        Carbon $start,
        float $totalHours
    ): array {
        $blocks = [];
        $lunchStart = $start->copy()->setTimeFromTimeString(self::BUSINESS_HOURS[0]['end']);
        $lunchEnd = $start->copy()->setTimeFromTimeString(self::BUSINESS_HOURS[1]['start']);

        // Bloque 1: desde el inicio hasta el almuerzo
        $block1Duration = $start->floatDiffInHours($lunchStart, false);
        $maxOrder = TareaBloque::where('tarea_id', $tarea->id)->max('orden') ?? 0;

        $blocks[] = TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $userId,
            'scheduled_by' => auth()->id(),
            'inicio' => $start,
            'fin' => $lunchStart,
            'orden' => $maxOrder + 1,
        ]);

        // Bloque 2: después del almuerzo con el tiempo restante
        $remainingHours = $totalHours - $block1Duration;
        if ($remainingHours > 0) {
            $blocks[] = TareaBloque::create([
                'id' => (string) Str::uuid(),
                'tarea_id' => $tarea->id,
                'user_id' => $userId,
                'scheduled_by' => auth()->id(),
                'inicio' => $lunchEnd,
                'fin' => $lunchEnd->copy()->addHours($remainingHours),
                'orden' => $maxOrder + 2,
            ]);
        }

        return $blocks;
    }

    /**
     * Método principal para crear bloques programados
     * Maneja automáticamente: validación de fechas pasadas, ajuste a franjas laborales,
     * y división cuando cruza el almuerzo
     */
    public function createScheduledBlocks(
        TareaServicio $tarea,
        int $userId,
        Carbon $requestedStart
    ): array {
        // 1. Ajustar al horario laboral (incluye validación de fecha pasada)
        $adjustedStart = $this->adjustToBusinessHours($requestedStart);

        // 2. Calcular duración y hora de fin tentativa
        $durationHours = (float) $tarea->tiempo_estimado_h;
        $tentativeEnd = $adjustedStart->copy()->addHours($durationHours);

        // 3. Verificar si cruza el almuerzo (12:15-13:45)
        if ($this->crossesLunchBreak($adjustedStart, $tentativeEnd)) {
            // Dividir en múltiples bloques
            return $this->splitAcrossLunchBreak($tarea, $userId, $adjustedStart, $durationHours);
        }

        // 4. Crear bloque simple (no cruza almuerzo)
        $maxOrder = TareaBloque::where('tarea_id', $tarea->id)->max('orden') ?? 0;

        return [
            TareaBloque::create([
                'id' => (string) Str::uuid(),
                'tarea_id' => $tarea->id,
                'user_id' => $userId,
                'scheduled_by' => auth()->id(),
                'inicio' => $adjustedStart,
                'fin' => $tentativeEnd,
                'orden' => $maxOrder + 1,
            ])
        ];
    }
}
