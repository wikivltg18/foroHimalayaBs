<?php

namespace Database\Seeders;

use App\Models\EstadoTarea;
use Illuminate\Database\Seeder;

class EstadoTareaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EstadoTarea::insert([
            ['nombre' => 'Programada', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En Progreso', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En revisión', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Finalizada', 'created_at' => now(), 'update_at' => now()],
        ]);
    }
}