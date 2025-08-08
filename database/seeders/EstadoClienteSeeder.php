<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstadoClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('estado_clientes')->insert([
            [
                'nombre' => 'Activo',
                'descripcion' => 'Cliente con actividades recientes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Inactivo',
                'descripcion' => 'Cliente sin actividad en los últimos meses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Suspendido',
                'descripcion' => 'Cuenta suspendida temporalmente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

}
