<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\{Cliente, Servicio, MapaDelCliente, MapaArea};

class MapaClienteController extends Controller
{
    private function assertPertenencia(Cliente $cliente, Servicio $servicio): void
    {
        abort_unless($servicio->cliente_id === $cliente->id, 404);
    }

    public function show(Cliente $cliente, Servicio $servicio)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('consultar mapa del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $mapa = $servicio->mapa()->with(['mapaAreas.area'])->firstOrCreate(['servicio_id' => $servicio->id]);

        return response()->json([
            'mapa'  => $mapa->only(['id', 'servicio_id']),
            'areas' => $mapa->mapaAreas->map(fn($m) => [
                'id'                 => $m->id,
                'area_id'            => $m->area_id,
                'area_nombre'        => $m->area->nombre ?? null,
                'horas_contratadas'  => (float)$m->horas_contratadas,
            ]),
        ]);
    }

    public function upsertAreas(Request $r, Cliente $cliente, Servicio $servicio)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('modificar mapa del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $data = $r->validate([
            'items' => 'required|array|min:1',
            'items.*.area_id' => 'required|exists:areas,id',
            'items.*.horas_contratadas' => 'required|numeric|min:0',
        ]);

        $mapa = $servicio->mapa()->firstOrCreate(['servicio_id' => $servicio->id]);

        DB::transaction(function () use ($data, $mapa) {
            foreach ($data['items'] as $row) {
                MapaArea::updateOrCreate(
                    ['mapa_del_cliente_id' => $mapa->id, 'area_id' => $row['area_id']],
                    ['horas_contratadas'   => $row['horas_contratadas']]
                );
            }
        });

        return response()->json(['ok' => true]);
    }

    public function destroyArea(Cliente $cliente, Servicio $servicio, $areaId)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('modificar mapa del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $mapa = $servicio->mapa()->firstOrCreate(['servicio_id' => $servicio->id]);

        $deleted = MapaArea::where('mapa_del_cliente_id', $mapa->id)
            ->where('area_id', $areaId)->delete();

        return response()->json(['ok' => (bool)$deleted]);
    }
}