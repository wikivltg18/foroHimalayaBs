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
    $busqueda = $request->input('buscar');

    $areas = Area::query()
        ->when($busqueda, function ($query, $busqueda) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        })
        ->orderBy('nombre')
        ->paginate(5)
        ->appends(['buscar' => $busqueda]); // mantiene el filtro en los enlaces

    return view('equipo.areas.index', compact('areas', 'busqueda'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('equipo.areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        Area::create($request->all());

        return redirect()->route('equipo.areas.index')->with('success', 'Área creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        return view('equipo.areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        $area->update($request->all());
        return redirect()->route('equipo.areas.index')->with('success', 'Área actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('equipo.areas.index')->with('success', 'Área eliminada.');
    }

}
