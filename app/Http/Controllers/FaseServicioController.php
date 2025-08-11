<?php

namespace App\Http\Controllers;

use App\Models\FaseServicio;
use Illuminate\Http\Request;

class FaseServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $paginator = FaseServicio::with([
            'tipoServicio:id,modalidad_id,nombre',
            'tipoServicio.modalidad:id,nombre'
        ])
        ->orderByDesc('id')
        ->paginate(5); // <-- 5 por página

    // Mapea los items pero conserva metadatos del paginator
    $paginator->setCollection(
        $paginator->getCollection()->map(fn($f) => [
            'id'        => $f->id,
            'fase'      => $f->nombre,
            'tipo'      => $f->tipoServicio?->nombre,
            'modalidad' => $f->tipoServicio?->modalidad?->nombre,
        ])
    );

    return response()->json($paginator);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'tipo_servicio_id' => 'required|exists:tipo_servicios,id',
        'nombre' => 'required|string|max:150',
        'descripcion' => 'nullable|string'
    ]);
    $fase = FaseServicio::create($data);
    return response()->json(['data' => $fase], 201);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $faseDeServicio = FaseServicio::findOrFail($id);
        $faseDeServicio->update($request->all());

        return response()->json($faseDeServicio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $faseDeServicio = FaseServicio::findOrfail($id);
        $faseDeServicio->delete();
        return response()->json(null, 204);
    }
}