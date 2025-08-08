<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Limpiar cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        
        // Crear permisos para el modelo cargos
        Permission::create(['name' => 'registrar cargo']);
        Permission::create(['name' => 'consultar cargos']);
        Permission::create(['name' => 'modificar cargo']);
        Permission::create(['name' => 'eliminar cargo']);

        // Crear permisos para el modelo clientes
        Permission::create(['name' => 'registrar cliente']);
        Permission::create(['name' => 'consultar clientes']);
        Permission::create(['name' => 'modificar cliente']);
        Permission::create(['name' => 'eliminar cliente']);

        //Crear permisos para el modelo usuarios
        Permission::create(['name' => 'registrar usuario']);
        Permission::create(['name' => 'modificar usuario']);
        Permission::create(['name' => 'eliminar usuario']);
        Permission::create(['name' => 'consultar usuarios']);

        // Crear permisos para el modelo áreas
        Permission::create(['name' => 'registrar área']);
        Permission::create(['name' => 'consultar áreas']);
        Permission::create(['name' => 'modificar área']);
        Permission::create(['name' => 'eliminar área']);

        //Crear permisos para el modelo roles
        Permission::create(['name' => 'registrar rol']);
        Permission::create(['name' => 'consultar roles']);
        Permission::create(['name' => 'modificar rol']);
        Permission::create(['name' => 'eliminar rol']);

        //Crear permisos para el modelo roles
        Permission::create(['name' => 'registrar rol']);
        Permission::create(['name' => 'consultar roles']);
        Permission::create(['name' => 'modificar rol']);
        Permission::create(['name' => 'eliminar rol']);

        // Crear permisos para el modelo Tipos y fases de servicios
        Permission::create(['name' =>'registrar tipo de servicio' ]);
        Permission::create(['name' =>'modificar tipo de servicio' ]);
        Permission::create(['name' =>'eliminar tipo de servicio' ]);
        Permission::create(['name' =>'registrar fase de servicio' ]);
        Permission::create(['name' =>'modificar fase de servicio' ]);
        Permission::create(['name' =>'eliminar fase de servicio' ]);

        // Crear permisos para el modelo Servicios
        Permission::create(['name' =>'crear servicio']);
        Permission::create(['name' =>'consultar servicios']);
        Permission::create(['name' =>'actualizar servicio']);
        Permission::create(['name' =>'eliminar servicio']);

        // Crear permisos para el modelo Equipos dedicados
        Permission::create(['name' =>'crear equipo dedicado']);
        Permission::create(['name' =>'consultar equipo dedicado']);
        Permission::create(['name' =>'actualizar equipo dedicado']);
        Permission::create(['name' =>'eliminar equipo dedicado']);

        // Crear roles y asignar permisos
        $superadministrador = Role::create(['name' => 'superadministrador']);
        $administrador = Role::create(['name' => 'administrador']);

        $superadministrador->givePermissionTo(Permission::all());
        $administrador->givePermissionTo(Permission::all());

    }
}
