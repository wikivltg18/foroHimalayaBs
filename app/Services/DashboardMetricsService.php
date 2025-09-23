<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\TareaServicio;
use App\Models\TareaTimeLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardMetricsService
{
    /**
     * KPIs principales en un rango de fechas.
     * Filtros opcionales: cliente_id, servicio_id, usuario_id, area_id.
     */
    public function kpis(array $filters): array
    {
        $desde = $filters['desde'] ?? Carbon::now()->subDays(30);
        $hasta = $filters['hasta'] ?? Carbon::now();
        $clienteId  = $filters['cliente_id']  ?? null;
        $servicioId = $filters['servicio_id'] ?? null;
        $usuarioId  = $filters['usuario_id']  ?? null;
        $areaId     = $filters['area_id']     ?? null;

        // Base query para tareas (puedes unir con tablas de servicio/cliente si necesitas filtrar)
        $tareas = TareaServicio::query()
            ->when($areaId, fn($q) => $q->where('id_area', $areaId));

        // Throughput: finalizadas en rango
        $throughput = (clone $tareas)
            ->whereNotNull('finalizada_at')
            ->whereBetween('finalizada_at', [$desde, $hasta])
            ->count();

        // WIP actual (estados "en progreso" o los que definas)
        $wip = (clone $tareas)
            ->whereNull('finalizada_at')
            ->whereIn('id_estado', \App\Models\EstadoTarea::wipIds())
            ->count();

        // Horas registradas por logs en rango
        $horas = TareaTimeLog::query()
            ->whereBetween('started_at', [$desde, $hasta])
            ->when($usuarioId, fn($q) => $q->where('usuario_id', $usuarioId))
            ->sum('duracion_h');

        // Accuracy estimación vs real (sobre tareas con logs)
        $accuracy = DB::table('tarea_tablero_servicio as t')
            ->leftJoin('tarea_time_logs as l', 'l.tarea_id', '=', 't.id')
            ->selectRaw('t.id, COALESCE(SUM(l.duracion_h),0) as real_h, COALESCE(MAX(t.tiempo_estimado),0) as est_h')
            ->when($areaId, fn($q) => $q->where('t.id_area', $areaId))
            ->groupBy('t.id')
            ->get();

        $estTotal = 0;
        $realTotal = 0;
        foreach ($accuracy as $row) {
            $estTotal  += (float) $row->est_h;
            $realTotal += (float) $row->real_h;
        }
        $deltaHoras = $realTotal - $estTotal;
        $accuracyPct = $estTotal > 0 ? round(100 * (1 - abs($deltaHoras) / $estTotal), 2) : null;

        // SLA: % finalizadas antes o en fecha de entrega
        $slaQuery = (clone $tareas)
            ->whereNotNull('finalizada_at')
            ->whereNotNull('fecha_de_entrega');

        $slaTotal = (clone $slaQuery)->count();
        $slaOnTime = (clone $slaQuery)
            ->whereColumn('finalizada_at', '<=', 'fecha_de_entrega')
            ->count();
        $slaPct = $slaTotal > 0 ? round(100 * $slaOnTime / $slaTotal, 2) : null;

        return [
            'desde'        => $desde,
            'hasta'        => $hasta,
            'throughput'   => $throughput,
            'horas'        => round($horas, 2),
            'wip'          => $wip,
            'estimado_h'   => round($estTotal, 2),
            'real_h'       => round($realTotal, 2),
            'delta_h'      => round($deltaHoras, 2),
            'accuracy_pct' => $accuracyPct,   // cercanía promedio al estimado
            'sla_pct'      => $slaPct,
        ];
    }

    /**
     * Serie diaria de throughput (tareas finalizadas por día).
     */
    public function throughputDaily(Carbon $desde, Carbon $hasta): array
    {
        $rows = TareaServicio::query()
            ->selectRaw('DATE(finalizada_at) as d, COUNT(*) as n')
            ->whereNotNull('finalizada_at')
            ->whereBetween('finalizada_at', [$desde, $hasta])
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return $rows->map(fn($r) => ['date' => $r->d, 'value' => (int)$r->n])->all();
    }

    /**
     * Percentiles de cycle time (en horas) para el periodo.
     * Cycle time = finalizada_at - created_at (o fecha_de_creacion si la usas).
     */
    public function cycleTimePercentiles(Carbon $desde, Carbon $hasta): array
    {
        $hours = TareaServicio::query()
            ->whereNotNull('finalizada_at')
            ->whereBetween('finalizada_at', [$desde, $hasta])
            ->get()
            ->map(function ($t) {
                $start = $t->created_at ?? $t->fecha_de_creacion;
                return $start ? $t->finalizada_at->diffInHours($start) : null;
            })
            ->filter()
            ->sort()
            ->values();

        $pct = fn($p) => $hours->isEmpty() ? null :
            $hours[(int) floor(($p / 100) * (count($hours) - 1))];

        return [
            'p50_h' => $pct(50),
            'p75_h' => $pct(75),
            'p90_h' => $pct(90),
        ];
    }
}