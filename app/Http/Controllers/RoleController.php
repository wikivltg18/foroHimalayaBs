<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('consultar roles')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Obtener el término de búsqueda si existe
        $busqueda = $request->input('buscar');

        // Consulta con filtro de búsqueda
        $roles = Role::query()
            ->when($busqueda, function ($query, $busqueda) {
                $query->where('name', 'like', "%{$busqueda}%");
            })
            ->orderBy('name') // Ordenar alfabéticamente por nombre
            ->paginate(5) // Paginación de 5 roles por página
            ->appends(['buscar' => $busqueda]); // Mantener el término de búsqueda en la paginación

    // Retornar la vista con los roles y el término de búsqueda
    return view('equipo.roles.index', compact('roles', 'busqueda'));
    }

    public function create()
    {
        if (!Auth::user()->can('registrar rol')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Retornar la vista para crear un nuevo rol
        return view('equipo.roles.create');
    }

    // Almacenar un nuevo rol en la base de datos
    public function store(Request $request)
    {
        if (!Auth::user()->can('registrar rol')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Validar la entrada
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);
        // Crear el nuevo rol
        Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);
        // Redirigir con un mensaje de éxito
        return redirect()->route('equipo.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        if (!Auth::user()->can('modificar rol')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Retornar la vista para editar el rol
        return view('equipo.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        if (!Auth::user()->can('modificar rol')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Validar la entrada
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);
        // Actualizar el rol
        $role->update([
            'name' => $request->name,
        ]);
        // Redirigir con un mensaje de éxito
        return redirect()->route('equipo.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if (!Auth::user()->can('eliminar rol')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Eliminar el rol
        $role->delete();
        // Redirigir con un mensaje de éxito
        return redirect()->route('equipo.roles.index')->with('success', 'Rol eliminado correctamente.');
    }

}