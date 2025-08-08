<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('buscar');

    $roles = Role::query()
        ->when($busqueda, function ($query, $busqueda) {
            $query->where('name', 'like', "%{$busqueda}%");
        })
        ->orderBy('name')
        ->paginate(5)
        ->appends(['buscar' => $busqueda]); // mantiene el filtro en los enlaces

    return view('equipo.roles.index', compact('roles', 'busqueda'));
    }

    public function create()
    {
        return view('equipo.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return redirect()->route('equipo.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        return view('equipo.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        return redirect()->route('equipo.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('equipo.roles.index')->with('success', 'Rol eliminado correctamente.');
    }

}