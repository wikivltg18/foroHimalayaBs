<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

/**
 * Controlador para la gestión de permisos de roles
 * 
 * Este controlador maneja la asignación y actualización de permisos a roles
 * utilizando el paquete spatie/laravel-permission.
 */
class PermisoController extends Controller
{
    /**
     * Muestra la interfaz de asignación de permisos
     * 
     * Recupera todos los roles y permisos del sistema, así como las asignaciones
     * actuales de permisos a roles para mostrarlos en la interfaz.
     *
     * @return \Illuminate\View\View Vista con los datos de roles y permisos
     */
    public function asignarPermisos()
    {
        if (!Auth::user()->can('asignar permisos')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        // Obtener todos los roles disponibles en el sistema
        $roles = Role::all();

        // Obtener todos los permisos disponibles en el sistema
        $permissions = Permission::all();

        // Obtener un mapeo de los permisos asignados a cada rol
        // La estructura resultante será: ['role_id' => ['permission_id1', 'permission_id2', ...]]
        $rolePermissions = Role::with('permissions')->get()->pluck('permissions.*.id', 'id');

        // Retornar la vista con los datos necesarios
        return view('permisos.index', compact('roles', 'permissions', 'rolePermissions'));
    }

    /**
     * Actualiza los permisos asignados a un rol específico
     *
     * Este método maneja la actualización de permisos para un rol determinado.
     * Utiliza una transacción de base de datos para garantizar la integridad
     * de los datos y proporciona logging detallado para facilitar el debugging.
     *
     * @param Request $request Solicitud HTTP con los permisos a asignar
     * @param int $roleId ID del rol a actualizar
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado de la operación
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el rol no existe
     * @throws \Exception Si hay errores durante la actualización
     */
    public function updatePermissions(Request $request, $roleId)
    {
        if (!Auth::user()->can('asignar permisos')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        try {
            // Registrar el inicio de la operación y los datos recibidos
            \Log::info('Iniciando actualización de permisos para el rol: ' . $roleId);
            \Log::info('Datos recibidos:', $request->all());

            // Validar el request
            $validated = $request->validate([
                'permissions' => 'present|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            \Log::info('Validación exitosa. Permisos a asignar:', $validated['permissions'] ?? []);

            DB::beginTransaction();
            
            // Encontrar el rol
            $role = Role::findOrFail($roleId);
            \Log::info('Rol encontrado:', ['role_id' => $role->id, 'role_name' => $role->name]);
            
            // Obtener los nuevos permisos como nombres
            $newPermissions = Permission::whereIn('id', $validated['permissions'] ?? [])->pluck('name')->toArray();
            \Log::info('Nombres de permisos a asignar:', $newPermissions);
            
            // Obtener permisos actuales antes de sincronizar
            $currentPermissions = $role->permissions->pluck('name')->toArray();
            \Log::info('Permisos actuales:', $currentPermissions);
            
            // Sincronizar los permisos del rol usando nombres
            $role->syncPermissions($newPermissions);
            
            // Verificar que los permisos se asignaron correctamente
            $updatedPermissions = $role->fresh()->permissions->pluck('id')->toArray();
            \Log::info('Permisos actualizados:', $updatedPermissions);
            
            DB::commit();
            
            // Retornar una respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados correctamente.',
                'data' => [
                    'roleId' => $roleId,
                    'permissions' => $updatedPermissions
                ]
            ])->header('Content-Type', 'application/json');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            \Log::error('Rol no encontrado:', ['role_id' => $roleId]);
            return response()->json([
                'success' => false,
                'message' => 'El rol especificado no existe.'
            ], 404)->header('Content-Type', 'application/json');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            \Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422)->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al actualizar permisos:', [
                'message' => $e->getMessage(),
                'role_id' => $roleId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al actualizar los permisos: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}