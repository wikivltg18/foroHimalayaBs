<?php

namespace Database\Seeders;

use App\Models\TipoContrato;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipoContratoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Equipo dedicado',
                'descripcion' => 'Contrato para asignación de equipo exclusivo al cliente',
                'id_cliente' => 4
            ],
            [
                'nombre' => 'Servicios',
                'descripcion' => 'Contrato para prestación de servicios variados',
                'id_cliente' => 4
            ],
            [
                'nombre' => 'Equipo dedicado',
                'descripcion' => 'Contrato para asignación de equipo exclusivo al cliente',
                'id_cliente' => 6
            ],
            [
                'nombre' => 'Servicios',
                'descripcion' => 'Contrato para prestación de servicios variados',
                'id_cliente' => 7
            ],
            [
                'nombre' => 'Equipo dedicado',
                'descripcion' => 'Contrato para asignación de equipo exclusivo al cliente',
                'id_cliente' => 8
            ],
            [
                'nombre' => 'Servicios',
                'descripcion' => 'Contrato para prestación de servicios variados',
                'id_cliente' => 7
            ]
        ];

        foreach ($tipos as $tipo) {
            TipoContrato::create($tipo);
        }

    }
}
