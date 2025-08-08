<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['nombre' => 'Diseño', 'descripcion' => 'Área enfocada en diseño gráfico y experiencia visual.'],
            ['nombre' => 'Contenido', 'descripcion' => 'Creación de textos, artículos y contenido multimedia.'],
            ['nombre' => 'Digital Performance', 'descripcion' => 'Optimización de campañas digitales y resultados.'],
            ['nombre' => 'Desarrollo', 'descripcion' => 'Programación y desarrollo de software y plataformas.'],
            ['nombre' => 'Creatividad', 'descripcion' => 'Generación de ideas innovadoras para proyectos.'],
            ['nombre' => 'Estrategia', 'descripcion' => 'Planificación y ejecución de estrategias empresariales.'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

    }
}
