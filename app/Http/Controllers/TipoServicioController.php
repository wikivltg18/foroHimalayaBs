<?php

namespace App\Http\Controllers;

use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoServicioController extends Controller
{
    /**
     * Muestra una lista de todos los tipos de servicio.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Evaluar si el usuario tiene permiso para consultar tipos de servicio
        if (!Auth::user()->can('consultar tipo de servicio')) {
            return response()->json(['redirect' => route('dashboard'),'error' => 'No tienes acceso a este módulo.']);
        }


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
    /**
     * Almacena un nuevo tipo de servicio en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Evaluar si el usuario tiene permiso para registrar tipo de servicio
        if (!Auth::user()->can('registrar tipo de servicio')) {
            return response()->json(['redirect' => route('dashboard'),'error' => 'No tienes acceso a este módulo.']);
        }

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
    /**
     * Actualiza un tipo de servicio específico en la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Evaluar si el usuario tiene permiso para modificar tipo de servicio
        if (!Auth::user()->can('modificar tipo de servicio')) {
            return response()->json(['redirect' => route('dashboard'),'error' => 'No tienes acceso a este módulo.']);
        }

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
    /**
     * Elimina un tipo de servicio específico de la base de datos.
     *
     * @param TipoServicio $tipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TipoServicio $tipo)
    {
        // Evaluar si el usuario tiene permiso para eliminar tipo de servicio
        if (!Auth::user()->can('eliminar tipo de servicio')) {
            return response()->json(['redirect' => route('dashboard'),'error' => 'No tienes acceso a este módulo.']);
        }

        // Eliminar las fases asociadas al tipo de servicio
        $tipo->fases()->delete();  // Esto eliminará todas las fases asociadas al tipo

    // Eliminar el tipo de servicio
    $tipo->delete();

    return response()->json(null, 204);
}
}