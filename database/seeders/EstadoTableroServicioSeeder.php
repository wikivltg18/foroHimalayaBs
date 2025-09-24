<?php

namespace Database\Seeders;

use App\Models\EstadoTableroServicio;
use Illuminate\Database\Seeder;

class EstadoTableroServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EstadoTableroServicio::insert([
            ['nombre' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Terminado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}