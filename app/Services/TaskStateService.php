<?php

namespace App\Services;

use App\Models\TareaEstadoHistorial;
use App\Models\TareaServicio;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskStateService
{
    /**
     * Cambiar estado de una tarea y registrar historial.
     */
    public function changeState(TareaServicio $tarea, int $nuevoEstadoId, int $usuarioId): TareaServicio
    {
        return DB::transaction(function () use ($tarea, $nuevoEstadoId, $usuarioId) {
            $anterior = $tarea->id_estado_tarjeta;

            // Evita escribir si no hay cambio
            if ($anterior === $nuevoEstadoId) {
                return $tarea;
            }

            $tarea->forceFill(['id_estado_tarjeta' => $nuevoEstadoId])->save();

            TareaEstadoHistorial::create([
                'id'                 => (string) Str::uuid(),
                'tarea_id'           => $tarea->getKey(),
                'cambiado_por'       => $usuarioId,
                'estado_id_anterior' => $anterior,
                'estado_id_nuevo'    => $nuevoEstadoId,
            ]);

            return $tarea->refresh();
        });
    }

    /**
     * Marcar tarea como finalizada (y opcionalmente moverla de columna/estado).
     */
    public function finalize(TareaServicio $tarea, int $usuarioId, ?int $estadoFinalizadaId = null): TareaServicio
    {
        return DB::transaction(function () use ($tarea, $usuarioId, $estadoFinalizadaId) {
            $now = Carbon::now();

            $updates = [
                'finalizada_at'     => $now,
                'finalizada_por'    => $usuarioId,
            ];

            if ($estadoFinalizadaId && $tarea->id_estado_tarjeta !== $estadoFinalizadaId) {
                $this->changeState($tarea, $estadoFinalizadaId, $usuarioId, 'Finalización de tarea');
            }

            $tarea->forceFill($updates)->save();

            return $tarea->refresh();
        });
    }

    /**
     * Reabrir una tarea finalizada.
     */
    public function reopen(TareaServicio $tarea, int $usuarioId, int $estadoReaperturaId): TareaServicio
    {
        return DB::transaction(function () use ($tarea, $usuarioId, $estadoReaperturaId) {
            $tarea->forceFill([
                'finalizada_at'        => null,
                'finalizada_por'       => null,
                'archivada'            => false,
            ])->save();

            $this->changeState($tarea, $estadoReaperturaId, $usuarioId);

            return $tarea->refresh();
        });
    }
}