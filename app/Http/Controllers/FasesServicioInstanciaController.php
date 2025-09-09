<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\FaseServicio;                // <— tu modelo de PLANTILLA (ya existente)
use App\Models\FaseDeServicioInstancia;     // <— modelo de instancias (con SoftDeletes)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FasesServicioInstanciaController extends Controller
{
    private function assertPertenencia(Cliente $cliente, Servicio $servicio): void
    {
        abort_unless($servicio->cliente_id === $cliente->id, 404);
    }

    public function index(Cliente $cliente, Servicio $servicio)
    {
        $this->assertPertenencia($cliente, $servicio);

        // Permiso de lectura (ajusta el nombre al tuyo)
        if (!Auth::user()->can('consultar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $fases = $servicio->fases()->get(); // ordenadas por posicion (definido en relación)
        return response()->json(['data' => $fases]);
    }

    // Crear fase: desde plantilla (fase_servicio_id) o personalizada (nombre/descripcion)
    public function store(Request $r, Cliente $cliente, Servicio $servicio)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('registrar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $data = $r->validate([
            'fase_servicio_id' => 'nullable|exists:fase_servicios,id', // usa tu nombre real de tabla
            'nombre'           => 'nullable|string|max:150',
            'descripcion'      => 'nullable|string|max:255',
        ]);

        $pos = (int) FaseDeServicioInstancia::where('servicio_id', $servicio->id)
            ->whereNull('deleted_at')->max('posicion') + 1;

        if (!empty($data['fase_servicio_id'])) {
            $tpl = FaseServicio::findOrFail($data['fase_servicio_id']);
            $fase = FaseDeServicioInstancia::create([
                'servicio_id'          => $servicio->id,
                'fase_de_servicio_id'  => $tpl->id,          // referencia a la plantilla
                'nombre'               => $tpl->nombre,
                'descripcion'          => $tpl->descripcion,
                'posicion'             => $pos,
            ]);
        } else {
            $fase = FaseDeServicioInstancia::create([
                'servicio_id'          => $servicio->id,
                'fase_de_servicio_id'  => null,
                'nombre'               => $data['nombre'] ?? 'Sin título',
                'descripcion'          => $data['descripcion'] ?? null,
                'posicion'             => $pos,
            ]);
        }

        return response()->json(['data' => $fase], 201);
    }

    public function update(Request $r, Cliente $cliente, Servicio $servicio, FaseDeServicioInstancia $fase)
    {
        $this->assertPertenencia($cliente, $servicio);
        abort_unless($fase->servicio_id === $servicio->id, 404);

        if (!Auth::user()->can('modificar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $data = $r->validate([
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $fase->update($data);
        return response()->json(['data' => $fase]);
    }

    // Soft delete (con recompactado de posiciones)
    public function destroy(Cliente $cliente, Servicio $servicio, FaseDeServicioInstancia $fase)
    {
        $this->assertPertenencia($cliente, $servicio);
        abort_unless($fase->servicio_id === $servicio->id, 404);

        if (!Auth::user()->can('eliminar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $fase->delete(); // soft
        $this->recompactarPosiciones($servicio->id);

        return response()->json(null, 204);
    }

    // Drag & drop
    public function reordenar(Request $r, Cliente $cliente, Servicio $servicio)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('modificar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $data = $r->validate([
            'orden' => 'required|array|min:1',           // [{id, posicion}]
            'orden.*.id' => 'required|integer|exists:fases_de_servicio_instancia,id',
            'orden.*.posicion' => 'required|integer|min:1',
        ]);

        $ids = collect($data['orden'])->pluck('id');
        $valid = FaseDeServicioInstancia::where('servicio_id', $servicio->id)
            ->whereNull('deleted_at')->whereIn('id', $ids)->count();

        if ($valid !== $ids->count()) {
            return response()->json(['error' => 'Fases inválidas para este servicio.'], 422);
        }

        DB::transaction(function () use ($data) {
            foreach ($data['orden'] as $item) {
                FaseDeServicioInstancia::where('id', $item['id'])
                    ->update(['posicion' => (int)$item['posicion']]);
            }
        });

        $this->recompactarPosiciones($servicio->id);
        return response()->json(['ok' => true]);
    }

    // Restaurar una fase eliminada (soft)
    public function restaurar(Cliente $cliente, Servicio $servicio, $faseId)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('modificar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $fase = FaseDeServicioInstancia::withTrashed()->findOrFail($faseId);
        abort_unless($fase->servicio_id === $servicio->id, 404);

        $fase->restore();
        $nuevaPos = (int) FaseDeServicioInstancia::where('servicio_id', $servicio->id)
            ->whereNull('deleted_at')->max('posicion') + 1;
        $fase->update(['posicion' => $nuevaPos]);

        return response()->json(['data' => $fase]);
    }

    // Purga definitiva (si no hay dependencias)
    public function purga(Cliente $cliente, Servicio $servicio, $faseId)
    {
        $this->assertPertenencia($cliente, $servicio);

        if (!Auth::user()->can('eliminar fases del servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        $fase = FaseDeServicioInstancia::withTrashed()->findOrFail($faseId);
        abort_unless($fase->servicio_id === $servicio->id, 404);

        // TODO: validar dependencias reales antes de purgar
        $fase->forceDelete();
        $this->recompactarPosiciones($servicio->id);

        return response()->json(null, 204);
    }

    private function recompactarPosiciones(int $servicioId): void
    {
        $fases = FaseDeServicioInstancia::where('servicio_id', $servicioId)
            ->whereNull('deleted_at')->orderBy('posicion')->get();

        foreach ($fases as $i => $f) {
            if ($f->posicion !== $i + 1) {
                $f->update(['posicion' => $i + 1]);
            }
        }
    }
}