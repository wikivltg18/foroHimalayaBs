<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class PermisoController extends Controller
{
    public function asignarPermisos()
    {
        // Obtener todos los roles y permisos
        $roles = Role::all();
        $permissions = Permission::all();
        // Obtener los permisos asignados a cada rol
        $rolePermissions = Role::with('permissions')->get()->pluck('permissions.*.id', 'id');

        // Retornar la vista con los datos
        return view('permisos.index', compact('roles', 'permissions', 'rolePermissions'));

    }

    public function updatePermissions(Request $request, Role $role)
    {
        // Validar los permisos recibidos
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        // Sincronizar los permisos del rol
        $role->syncPermissions($request->permissions ?? []);
        
        // Retornar una respuesta exitosa
        return response()->json(['message' => 'Permisos actualizados correctamente.']);
    }
}