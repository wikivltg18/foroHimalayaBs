<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TareaBloque;
use Illuminate\Support\Str;
use App\Models\TareaServicio;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncTaskBlockToCalendarJob;
use App\Jobs\RemoveSingleBlockFromCalendarJob;

class AgendaScheduler
{
    // Ventanas de trabajo
    const WINDOWS = [
        ['08:00','12:15'],
        ['13:45','18:00'],
    ];

    /** Programa una tarea para un user desde un inicio dado, partiendo en bloques. */
    public function schedule(TareaServicio $tarea, int $userId, Carbon $start, ?int $scheduledBy = null): void
    {
        // Limpia bloques previos de esa tarea para ese user (y eventos Google en Job separado)
        $this->unschedule($tarea->id, $userId);

        $durationMin = (int) round(((float) $tarea->tiempo_estimado_h) * 60);
        if ($durationMin <= 0) return;

        $cursor = $this->snapToWorkingWindow($start->copy());
        $bloques = [];

        while ($durationMin > 0) {
            $window = $this->windowFor($cursor);
            if (!$window) { $cursor = $this->nextDayStart($cursor); continue; }

            [$wStart, $wEnd] = $window;
            $free = $this->freeSegments($userId, $wStart, $wEnd);

            if (empty($free)) { $cursor = $wEnd; continue; }

            foreach ($free as [$segStart, $segEnd]) {
                if ($segEnd <= $cursor) continue;
                $segStart = max($segStart, $cursor);
                $segLen   = $segEnd->diffInMinutes($segStart);
                if ($segLen <= 0) continue;

                $takeMin = min($durationMin, $segLen);
                $bloques[] = [$segStart->copy(), $segStart->copy()->addMinutes($takeMin)];
                $durationMin -= $takeMin;

                $cursor = $segStart->copy()->addMinutes($takeMin);
                if ($durationMin <= 0) break;
            }

            if ($durationMin > 0 && $cursor < $wEnd) $cursor = $wEnd;
            if ($durationMin > 0 && $cursor >= $wEnd) $cursor = $this->nextDayStart($cursor);
        }

        DB::transaction(function () use ($tarea, $userId, $bloques, $scheduledBy) {
            foreach ($bloques as $i => [$ini, $fin]) {
                $row = TareaBloque::create([
                    'id'           => (string) Str::uuid(),
                    'tarea_id'     => $tarea->id,
                    'user_id'      => $userId,
                    'scheduled_by' => $scheduledBy,
                    'inicio'       => $ini,
                    'fin'          => $fin,
                    'orden'        => $i + 1,
                ]);

                // Sincroniza Google por bloque (Job asíncrono o sync según tu .env)
                dispatch(new SyncTaskBlockToCalendarJob($row->id))->onQueue('calendar');
            }

            if (!empty($bloques)) {
                $tarea->forceFill([
                    'programada_inicio' => $bloques[0][0],
                    'programada_fin'    => end($bloques)[1],
                ])->save();
            }
        });
    }

    /** Borra todos los bloques (y eventos Google) de esa tarea para ese user. */
    public function unschedule(string $tareaId, int $userId): void
    {
        $rows = TareaBloque::where('tarea_id', $tareaId)->where('user_id', $userId)->get();
        foreach ($rows as $row) {
            dispatch(new RemoveSingleBlockFromCalendarJob($row->id))->onQueue('calendar');
        }
        TareaBloque::where('tarea_id', $tareaId)->where('user_id', $userId)->delete();

        $remain = TareaBloque::where('tarea_id', $tareaId)->exists();
        if (!$remain) {
            TareaServicio::where('id', $tareaId)->update(['programada_inicio'=>null,'programada_fin'=>null]);
        }
    }

    // === utilidades de agenda ===

    private function snapToWorkingWindow(Carbon $c): Carbon
    {
        [$o1,$c1] = self::WINDOWS[0];
        [$o2,$c2] = self::WINDOWS[1];

        $open1  = $c->copy()->setTimeFromTimeString($o1);
        $close1 = $c->copy()->setTimeFromTimeString($c1);
        $open2  = $c->copy()->setTimeFromTimeString($o2);
        $close2 = $c->copy()->setTimeFromTimeString($c2);

        if ($c->lt($open1)) return $open1;
        if ($c->between($open1, $close1)) return $c;
        if ($c->between($close1, $open2)) return $open2;
        if ($c->between($open2, $close2)) return $c;

        return $this->nextDayStart($c);
    }

    private function nextDayStart(Carbon $c): Carbon
    {
        return $c->copy()->addDay()->setTimeFromTimeString(self::WINDOWS[0][0]);
    }

    private function windowFor(Carbon $c): ?array
    {
        foreach (self::WINDOWS as [$o,$cl]) {
            $start = $c->copy()->setTimeFromTimeString($o);
            $end   = $c->copy()->setTimeFromTimeString($cl);
            if ($c->between($start, $end, true)) return [$start, $end];
        }
        return null;
    }

    /**
     * Segmentos libres dentro de [winStart, winEnd] restando bloques existentes del usuario.
     * @return array<array{0:Carbon,1:Carbon}>
     */
    private function freeSegments(int $userId, Carbon $winStart, Carbon $winEnd): array
    {
        $busy = TareaBloque::where('user_id', $userId)
            ->where(function ($q) use ($winStart, $winEnd) {
                $q->whereBetween('inicio', [$winStart, $winEnd])
                  ->orWhereBetween('fin',   [$winStart, $winEnd])
                  ->orWhere(function ($qq) use ($winStart, $winEnd) {
                      $qq->where('inicio', '<=', $winStart)->where('fin', '>=', $winEnd);
                  });
            })
            ->orderBy('inicio')->get()
            ->map(fn($b) => [$b->inicio->copy(), $b->fin->copy()])->all();

        if (empty($busy)) return [[$winStart->copy(), $winEnd->copy()]];

        $free = [];
        $cursor = $winStart->copy();

        foreach ($busy as [$bS, $bE]) {
            if ($bE <= $cursor) continue;
            if ($bS > $cursor) $free[] = [$cursor->copy(), min($bS, $winEnd->copy())];
            $cursor = max($cursor, min($bE, $winEnd));
            if ($cursor >= $winEnd) break;
        }
        if ($cursor < $winEnd) $free[] = [$cursor->copy(), $winEnd->copy()];

        return array_values(array_filter($free, fn($p) => $p[1]->gt($p[0])));
    }
}