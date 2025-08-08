<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class PermisoController extends Controller
{
    public function asignarPermisos()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $rolePermissions = Role::with('permissions')->get()->pluck('permissions.*.id', 'id');

        return view('permisos.index', compact('roles', 'permissions', 'rolePermissions'));

    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return response()->json(['message' => 'Permisos actualizados correctamente.']);
    }

}
