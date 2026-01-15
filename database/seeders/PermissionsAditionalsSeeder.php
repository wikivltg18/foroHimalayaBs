<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsAditionalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Limpiar cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definir los nuevos permisos
        
        Permission::create(['name' => 'vincular google calendar']);
        Permission::create(['name' => 'configurar calendarios google']);
        Permission::create(['name' => 'consultar calendario personal']);
        Permission::create(['name' => 'consultar calendario equipo']);
    }
}
