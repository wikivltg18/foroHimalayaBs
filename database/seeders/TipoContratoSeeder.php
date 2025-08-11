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
            ],
            [
                'nombre' => 'Servicios',
                'descripcion' => 'Contrato para prestación de servicios variados',
            ]
        ];

        foreach ($tipos as $tipo) {
            TipoContrato::create($tipo);
        }

    }
}