<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsolidadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, \App\Models\Servicio $servicio)
    {
        // 13.4 - Solo Superadministrador (o permiso específico)
        if (!auth()->user()->hasRole('Superadministrador')) {
            abort(403, 'No tienes acceso a este módulo.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $selectedAreaId = $request->input('area_id');
        $selectedUsuarioId = $request->input('usuario_id');

        // Cargar relaciones necesarias
        $servicio->load(['cliente', 'mapa.mapaAreas.area']);

        // Obtener todas las áreas contratadas para este servicio
        $mapaAreas = $servicio->mapa ? $servicio->mapa->mapaAreas : collect();

        // Obtener todas las tareas vinculadas a este servicio para los cálculos de consumo
        // Tarea -> Columna -> Tablero -> Servicio
        $tablerosIds = $servicio->tableros()->pluck('id');
        
        $dataareas = [];
        $totalContratadas = 0;
        $totalConsumidas = 0;

        foreach ($mapaAreas as $mapaArea) {
            $area = $mapaArea->area;
            
            // Si hay filtro de área y no coincide, saltar
            if ($selectedAreaId && $area->id != $selectedAreaId) continue;

            // Calcular horas consumidas en esta área
            $consumidas = \App\Models\TareaTimeLog::whereHas('tarea.columna.tablero', function ($q) use ($servicio) {
                    $q->where('servicio_id', $servicio->id);
                })
                ->whereHas('tarea', function ($q) use ($area) {
                    $q->where('area_id', $area->id);
                })
                ->whereBetween('started_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->when($selectedUsuarioId, function ($q) use ($selectedUsuarioId) {
                    $q->where('usuario_id', $selectedUsuarioId);
                })
                ->sum('duracion_h');

            $contratadas = $mapaArea->horas_contratadas;
            $restantes = max(0, $contratadas - $consumidas);
            $porcentaje = $contratadas > 0 ? ($consumidas / $contratadas) * 100 : 0;

            $dataareas[] = [
                'area' => $area,
                'contratadas' => $contratadas,
                'consumidas' => $consumidas,
                'restantes' => $restantes,
                'porcentaje' => round($porcentaje, 2),
                'disponibles' => $contratadas // Asumiendo que contratadas = disponibles inicialmente
            ];

            $totalContratadas += $contratadas;
            $totalConsumidas += $consumidas;
        }

        $totalRestantes = max(0, $totalContratadas - $totalConsumidas);
        $totalPorcentaje = $totalContratadas > 0 ? ($totalConsumidas / $totalContratadas) * 100 : 0;

        // Para los filtros de la vista
        $areasFiltro = \App\Models\Area::orderBy('nombre')->get();
        $usuariosFiltro = \App\Models\User::orderBy('name')->get();

        return view('consolidado.index', compact(
            'servicio',
            'dataareas',
            'totalContratadas',
            'totalConsumidas',
            'totalRestantes',
            'totalPorcentaje',
            'startDate',
            'endDate',
            'areasFiltro',
            'usuariosFiltro',
            'selectedAreaId',
            'selectedUsuarioId'
        ));
    }

}
