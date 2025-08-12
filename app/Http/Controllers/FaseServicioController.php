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
    // Construcción de la consulta con relaciones
    $query = FaseServicio::with([
        'tipoServicio:id,modalidad_id,nombre',
        'tipoServicio.modalidad:id,nombre'
    ]);

    // Filtro por nombre de fase
    if ($request->filled('buscarFaseDeServicio')) {
        $query->where('nombre', 'like', '%' . $request->buscarFaseDeServicio . '%');
    }

    // Filtro por nombre de tipo de servicio
    if ($request->filled('buscarTipoDeServicio')) {
        $query->whereHas('tipoServicio', function ($q) use ($request) {
            $q->where('nombre', 'like', '%' . $request->buscarTipoDeServicio . '%');
        });
    }

    // Filtro por nombre de modalidad
    if ($request->filled('buscarModalidad')) {
        $query->whereHas('tipoServicio.modalidad', function ($q) use ($request) {
            $q->where('nombre', 'like', '%' . $request->buscarModalidad . '%');
        });
    }

    // Paginación y ordenamiento
    $paginator = $query->orderByDesc('id')->paginate(5); // 5 por página

    // Transformación de datos para la respuesta
    $paginator->setCollection(
        $paginator->getCollection()->map(fn($f) => [
            'id'        => $f->id,
            'fase'      => $f->nombre,
            'tipo'      => $f->tipoServicio?->nombre,
            'modalidad' => $f->tipoServicio?->modalidad?->nombre,
        ])
    );
    // Respuesta JSON
    return response()->json($paginator);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validación de datos
    $data = $request->validate([
        'tipo_servicio_id' => 'required|exists:tipo_servicios,id',
        'nombre' => 'required|string|max:150',
        'descripcion' => 'nullable|string'
    ]);
    
    // Creación de la fase de servicio
    $fase = FaseServicio::create($data);
    
    // Respuesta JSON con la nueva fase
    return response()->json(['data' => $fase], 201);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validación de datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);
        // Actualización de la fase de servicio
        $faseDeServicio = FaseServicio::findOrFail($id);
        $faseDeServicio->update($request->all());

        // Respuesta JSON con la fase actualizada
        return response()->json($faseDeServicio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Eliminación de la fase de servicio
        $faseDeServicio = FaseServicio::findOrfail($id);
        $faseDeServicio->delete();
        
        // Respuesta JSON sin contenido
        return response()->json(null, 204);
    }
}