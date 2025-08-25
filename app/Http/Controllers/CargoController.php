<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CargoController extends Controller
{
    /**
     * Muestra una lista paginada de todos los cargos.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Evaluar si el usuario tiene permiso para consultar cargos
        if (!Auth::user()->can('consultar cargos')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

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
    /**
     * Muestra el formulario para crear un nuevo cargo.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Evaluar si el usuario tiene permiso para registrar cargo
        if (!Auth::user()->can('registrar cargo')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        return view('equipo.cargos.create');
    }

    /**
     * Almacena un nuevo cargo en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Evaluar si el usuario tiene permiso para registrar cargo
        if (!Auth::user()->can('registrar cargo')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

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
    /**
     * Muestra el formulario para editar un cargo específico.
     *
     * @param Cargo $cargo
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Cargo $cargo)
    {
        // Evaluar si el usuario tiene permiso para modificar cargo
        if (!Auth::user()->can('modificar cargo')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        if (!$cargo) {
            return redirect()->route('equipo.cargos.index')
                ->withErrors(['error' => 'Cargo no encontrado.']);
        }

        return view('equipo.cargos.edit', compact('cargo'));
    }

    /**
     * Actualiza un cargo específico en la base de datos.
     *
     * @param Request $request
     * @param Cargo $cargo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Cargo $cargo)
    {
        // Evaluar si el usuario tiene permiso para modificar cargo
        if (!Auth::user()->can('modificar cargo')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

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
    /**
     * Elimina un cargo específico de la base de datos.
     *
     * @param Cargo $cargo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Cargo $cargo)
    {
        // Evaluar si el usuario tiene permiso para eliminar cargo
        if (!Auth::user()->can('eliminar cargo')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Verificar si el cargo tiene usuarios asociados
        if ($cargo->usuarios()->count() > 0) {
            return redirect()->route('equipo.cargos.index')
                ->withErrors(['error' => 'No se puede eliminar el cargo porque tiene usuarios asociados.']);
        }

        // Eliminar el cargo
        $cargo->delete();
        
        return redirect()->route('equipo.cargos.index')
            ->with('success', 'Cargo eliminado exitosamente.');
    }
}