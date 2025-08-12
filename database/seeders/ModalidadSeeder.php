<?php

namespace Database\Seeders;

use App\Models\Modalidad;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalidades =[
            ['nombre' => 'Fee', 'descripcion' => 'El cliente paga una cantidad fija periodicamente por un conjunto de servicios'],
            ['nombre' => 'Puntual', 'descripcion' => 'El cliente paga solo cuando necesita el servicio, sin compromiso de continuidad'],
        ];

        foreach($modalidades as $modalidad){
            Modalidad::create($modalidad);
        }
    }
}