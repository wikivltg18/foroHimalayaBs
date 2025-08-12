<?php

namespace Database\Seeders;

use App\Models\FaseServicio;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FaseServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fasesDeServicios = [[
                    'tipo_servicio_id' => 2,
                    'nombre' => 'Keyword research',
                    'descripcion' => 'Keyword research',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tipo_servicio_id' => 2,
                    'nombre' => 'Oportunidad de contenidos',
                    'descripcion' => 'Oportunidad de contenidos',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tipo_servicio_id' => 2,
                    'nombre' => 'Redacción y diseño del contenido ',
                    'descripcion' => 'Redacción y diseño del contenido ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tipo_servicio_id' => 2,
                    'nombre' => 'Publicación del contenido',
                    'descripcion' => 'Publicación del contenido',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]];

                foreach($fasesDeServicios as $faseDeServicio){
                    FaseServicio::create($faseDeServicio);
                }

    }
}