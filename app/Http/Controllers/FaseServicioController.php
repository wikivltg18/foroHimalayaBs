<?php

namespace App\Http\Controllers;

use App\Models\FaseServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaseServicioController extends Controller
{
    /**
     * Muestra una lista paginada de todas las fases de servicio.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Evaluar si el usuario tiene permiso para consultar fases de servicio
        if (!Auth::user()->can('consultar fase de servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

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
                'tipo_id'      => $f->tipoServicio?->id,
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
    /**
     * Almacena una nueva fase de servicio en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Evaluar si el usuario tiene permiso para registrar fase de servicio
        if (!Auth::user()->can('registrar fase de servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

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
    /**
     * Actualiza una fase de servicio específica en la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Evaluar si el usuario tiene permiso para modificar fase de servicio
        if (!Auth::user()->can('modificar fase de servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

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

    /**
     * Elimina una fase de servicio específica de la base de datos.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Evaluar si el usuario tiene permiso para eliminar fase de servicio
        if (!Auth::user()->can('eliminar fase de servicio')) {
            return response()->json(['redirect' => route('dashboard'), 'error' => 'No tienes acceso a este módulo.']);
        }

        try {
            // Eliminación de la fase de servicio
            $faseDeServicio = FaseServicio::findOrFail($id);
            $faseDeServicio->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            // Respuesta JSON sin contenido
            return response()->json([
                'error' => 'No se pudo eliminar la fase de servicio.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}