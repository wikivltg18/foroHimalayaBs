<?php

namespace Database\Seeders;

use App\Models\TipoServicio;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipoServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposDeServicios = [[
                    'modalidad_id' => 1,
                    'nombre' => 'Creación de parrilla',
                    'descripcion' => 'Planificación estratégica de contenidos para publicar en redes o medios digitales.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'modalidad_id' => 1,
                    'nombre' => 'SEO',
                    'descripcion' => 'Optimización de sitios web para mejorar su visibilidad en buscadores como Google.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'modalidad_id' => 1,
                    'nombre' => 'Pauta digital',
                    'descripcion' => 'Publicidad pagada en plataformas digitales como Meta Ads, Google Ads o TikTok.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'modalidad_id' => 1,
                    'nombre' => 'Soporte hosting',
                    'descripcion' => 'Asistencia técnica para mantener el sitio web activo, seguro y accesible.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'modalidad_id' => 2,
                    'nombre' => 'Diseño y desarrollo web',
                    'descripcion' => 'Creación visual y funcional de sitios web adaptados a las necesidades del cliente.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]];

                foreach($tiposDeServicios as $tipoDeServicio){
                    TipoServicio::create($tipoDeServicio);
                }

    }
}