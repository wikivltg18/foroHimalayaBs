<?php

namespace App\Http\Controllers;
use App\Models\TareaServicio;
use Illuminate\Http\Request;

class ForoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Validación de parámetros
        $validated = $request->validate([
            'cantidad' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
            'area_id'  => 'nullable|exists:areas,id',
        ]);

        $cantidad = $validated['cantidad'] ?? 5;

        // Determinar el área a filtrar
        $areaId = $user->id_area;
        if ($user->hasRole(['Administrador', 'Superadministrador']) && !empty($validated['area_id'])) {
            $areaId = $validated['area_id'];
        }

        // Obtener el nombre del área para el título
        $areaActual = \App\Models\Area::find($areaId);
        $areaNombre = $areaActual->nombre ?? 'Sin área';

        // Construcción de la consulta
        $query = TareaServicio::query()
            ->with(['columna.tablero.cliente', 'estado', 'area', 'usuario'])
            ->where('area_id', $areaId)
            ->where('usuario_id', $user->id);

        // Filtro de búsqueda
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhereHas('columna.tablero.cliente', function ($cq) use ($search) {
                      $cq->where('nombre', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Paginación con persistencia de filtros
        $tareas = $query->latest()->paginate($cantidad)->appends($request->query());

        $areaNombre = $user->area->nombre ?? 'Sin área';

        return view('foro.index', compact('tareas', 'areaNombre'));
    }

}
