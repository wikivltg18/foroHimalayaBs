<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cargos = [
            ['nombre' => 'Gerente', 'descripcion' => 'Responsable de la gestión general'],
            ['nombre' => 'Administrador', 'descripcion' => 'Responsable de la gestión operativa'],
            ['nombre' => 'Desarrollador', 'descripcion' => 'Encargado del desarrollo de software'],
            ['nombre' => 'Diseñador', 'descripcion' => 'Responsable del diseño visual y UX'],
            ['nombre' => 'Analista', 'descripcion' => 'Encargado del análisis de datos y requerimientos'],
        ];

        foreach ($cargos as $cargo) {
            Cargo::create($cargo);
        }
    }
}
