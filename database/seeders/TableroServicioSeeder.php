<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\TableroServicio;
use Illuminate\Database\Seeder;
use App\Models\EstadoTableroServicio;
use App\Models\ColumnaTableroServicio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TableroServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estado = EstadoTableroServicio::where('nombre', 'Activo')->first();
        if (!$estado) return;

        $tablero = TableroServicio::firstOrCreate(
            ['id' => Str::uuid()],
            [
                'nombre_del_tablero' => 'Tablero de Ejemplo',
                'servicio_id' => 28,
                'cliente_id' => 28,
                'estado_tablero_id' => $estado->id,
                'nombre_del_servicio' => 'Creación de parrillas de contenido',
                'nombre_cliente' => 'Comfandi Empresarial',
                'nombre_modalidad' => 'Puntual',
                'nombre_tipo_de_servicio' => 'Creación de parrilla'
            ]
        );

        $cols = ['Fidelización de clientes', 'Aumento de ventas', 'Promoción de eventos ', 'Reconocimiento de marca', 'Generación de leads'];
        foreach ($cols as $i => $c) {
            ColumnaTableroServicio::firstOrCreate([
                'tablero_servicio_id' => $tablero->id,
                'nombre_de_la_columna' => $c
            ], ['orden' => $i + 1]);
        }
    }
}