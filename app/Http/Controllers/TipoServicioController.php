<?php

namespace App\Http\Controllers;

use App\Models\TipoServicio;
use Illuminate\Http\Request;

class TipoServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtrar por modalidad_id si se proporciona en la solicitud
        $q = TipoServicio::query()->select('id','modalidad_id','nombre','descripcion');
        // Si se proporciona modalidad_id en la solicitud, filtrar por ese valor
        if ($request->filled('modalidad_id')) {
            $q->where('modalidad_id', $request->modalidad_id);
        }
    return response()->json(['data' => $q->orderBy('nombre')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'modalidad_id' => 'required|exists:modalidads,id',
        'nombre' => 'required|string|max:150',
        'descripcion' => 'nullable|string'
    ]);
    $tipo = TipoServicio::create($data);
    return response()->json(['data' => $tipo], 201);
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

        $tipoServicio = TipoServicio::findOrFail($id);
        $tipoServicio->update($request->all());

        return response()->json($tipoServicio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tipoServicio = TipoServicio::findOrFail($id);
        $tipoServicio->delete();
        return response()->json(null, 204);
    }
}