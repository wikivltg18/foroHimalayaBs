<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener parámetros de búsqueda
        $busqueda = $request->input('buscar');

        // Consulta con filtro de búsqueda
        $areas = Area::query()
            ->when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->orderBy('nombre') // Ordenar por nombre
            ->paginate(5) // Paginación de 5 por página
            ->appends(['buscar' => $busqueda]); // mantiene el filtro en los enlaces

        // Pasar los datos a la vista
        return view('equipo.areas.index', compact('areas', 'busqueda'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Retornar la vista del formulario de creación
        return view('equipo.areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        // Crear y guardar la nueva área
        Area::create($request->all());

        // Redirigir con mensaje de éxito
        return redirect()->route('equipo.areas.index')->with('success', 'Área creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        // Retornar la vista del formulario de edición con los datos del área
        return view('equipo.areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        // Actualizar y guardar los cambios del área
        $area->update($request->all());
        
        // Redirigir con mensaje de éxito
        return redirect()->route('equipo.areas.index')->with('success', 'Área actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy(Area $area)
    {
        // Eliminar el área
        $area->delete();
        
        // Redirigir con mensaje de éxito
        return redirect()->route('equipo.areas.index')->with('success', 'Área eliminada.');
    }

}