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

    // Obtener parámetros de búsqueda
    $busqueda = $request->input('buscar');
    
    // Consulta con filtro de búsqueda y paginación
    $cargos = Cargo::query()
        ->when($busqueda, function ($query, $busqueda) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        })
        ->orderBy('nombre') // Ordenar por nombre
        ->paginate(5) // 5 por defecto
        ->appends(['buscar' => $busqueda]); // mantiene el filtro en los enlaces

    // Pasar los datos a la vista
        return view('equipo.cargos.index', compact('cargos', 'busqueda'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Retornar la vista del formulario de creación
        return view('equipo.cargos.create');
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

        // Verificar si el nombre del cargo ya existe
        if (Cargo::where('nombre', $request->nombre)->exists()) {
            return redirect()->back()->withErrors(['nombre' => 'El cargo ya existe.']);
        }

        // Crear un nuevo cargo
        Cargo::create($request->all());

        // Redireccionar con mensaje de éxito
        return redirect()->route('equipo.cargos.index')->with('success', 'Cargo creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cargo $cargo)
    {
        // Retornar la vista del formulario de edición con el cargo seleccionado
        if (!$cargo) {
            return redirect()->route('equipo.cargos.index')->withErrors(['error' => 'Cargo no encontrado.']);
        }
        // Retornar la vista del formulario de edición
        return view('equipo.cargos.edit', compact('cargo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cargo $cargo)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
        ]);

        // Verificar si el nombre del cargo ya existe y no es el mismo que el actual
        if (Cargo::where('nombre', $request->nombre)->where('id', '!=', $cargo->id)->exists()) {
            return redirect()->back()->withErrors(['nombre' => 'El cargo ya existe.']);
        }
        // Actualizar el cargo
        $cargo->update($request->all());
        return redirect()->route('equipo.cargos.index')->with('success', 'Cargo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cargo $cargo)
    {
        // Eliminar el cargo
        $area->delete();
        
        // Verificar si el cargo tiene usuarios asociados
        if ($cargo->usuarios()->count() > 0) {
            return redirect()->route('equipo.cargos.index')->withErrors(['error' => 'No se puede eliminar el cargo porque tiene usuarios asociados.']);
        }

        // Redireccionar con mensaje de éxito
        return redirect()->route('equipo.index.index'->with('success', 'Cargo eliminado exitosamente.'));
    }
}