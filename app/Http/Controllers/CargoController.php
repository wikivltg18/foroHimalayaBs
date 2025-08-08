<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

    $busqueda = $request->input('buscar');

    $cargos = Cargo::query()
        ->when($busqueda, function ($query, $busqueda) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        })
        ->orderBy('nombre')
        ->paginate(5)
        ->appends(['buscar' => $busqueda]); // mantiene el filtro en los enlaces

        return view('equipo.cargos.index', compact('cargos', 'busqueda'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('equipo.cargos.create');
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

        Cargo::create($request->all());

        return redirect()->route('equipo.cargos.index')->with('success', 'Cargo creado exitosamente.');
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
    public function edit(Cargo $cargo)
    {
        return view('equipo.cargos.edit', compact('cargo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cargo $cargo)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        $cargo->update($request->all());
        return redirect()->route('equipo.cargos.index')->with('success', 'Cargo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cargo $cargo)
    {
        $area->delete();
        return redirect()->route('equipo.index.index'->with('success', 'Cargo eliminado exitosamente.'));
    }
}
