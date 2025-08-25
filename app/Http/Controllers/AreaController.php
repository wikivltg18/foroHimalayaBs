<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Muestra el listado de áreas.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Evaluar si el usuario tiene permiso para consultar áreas
        if (!Auth::user()->can('consultar áreas')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Obtener parámetros de búsqueda
        $busqueda = $request->input('buscar');

        // Consulta con filtro de búsqueda
        $areas = Area::query()
            ->when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->orderBy('nombre')
            ->paginate(5)
            ->appends(['buscar' => $busqueda]);

        return view('equipo.areas.index', compact('areas', 'busqueda'));
    }

    /**
     * Muestra el formulario para crear una nueva área.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Evaluar si el usuario tiene permiso para registrar área
        if (!Auth::user()->can('registrar área')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        return view('equipo.areas.create');
    }
    /**
     * Store a newly created resource in storage.
     */
    /**
     * Almacena una nueva área en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Evaluar si el usuario tiene permiso para registrar área
        if (!Auth::user()->can('registrar área')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        // Crear y guardar la nueva área
        Area::create($request->all());

        return redirect()->route('equipo.areas.index')
            ->with('success', 'Área creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar un área específica.
     *
     * @param Area $area
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Area $area)
    {
        // Evaluar si el usuario tiene permiso para modificar área
        if (!Auth::user()->can('modificar área')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        return view('equipo.areas.edit', compact('area'));
    }

    /**
     * Actualiza un área específica en la base de datos.
     *
     * @param Request $request
     * @param Area $area
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Area $area)
    {
        // Evaluar si el usuario tiene permiso para modificar área
        if (!Auth::user()->can('modificar área')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        // Actualizar el área
        $area->update($request->all());
        
        return redirect()->route('equipo.areas.index')
            ->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * Elimina un área específica de la base de datos.
     *
     * @param Area $area
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Area $area)
    {
        // Evaluar si el usuario tiene permiso para eliminar área
        if (!Auth::user()->can('eliminar área')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Eliminar el área
        $area->delete();
        
        return redirect()->route('equipo.areas.index')
            ->with('success', 'Área eliminada exitosamente.');
    }
}