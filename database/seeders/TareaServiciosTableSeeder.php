<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TareaServiciosTableSeeder extends Seeder
{
    public function run(): void
    {
        
         $carlos  = DB::table('users')->where('email', 'carlos06182016@gmail.com')->first();
         $laura   = DB::table('users')->where('email', 'laura.alejandra12@gmail.com')->first();

        // Si faltan, aborta silenciosamente (para no romper seed)
        if (! $carlos|| ! $laura) return;

        $tareas = [
            [
                'id'                => (string) Str::uuid(),
                'columna_id'        => 'fcac6b95-88a7-47e5-80f4-968d6f51a857',
                'estado_id'         => 1, // Programada
                'area_id'           => 4, // Desarrollo
                'usuario_id'        =>  $carlos->id, // asignada a Carlos
                'titulo'            => 'Implementar formulario de registro',
                'descripcion'       => '<p>Crear formulario con validación del lado del servidor.</p>',
                'tiempo_estimado_h' => 6.0, // 6 horas → el scheduler lo parte
                'fecha_de_entrega'  => now()->addDays(3)->toDateString(),
                'posicion'          => 1,
                'archivada'         => false,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'id'                => (string) Str::uuid(),
                'columna_id'        => 'fcac6b95-88a7-47e5-80f4-968d6f51a857',
                'estado_id'         => 1, 
                'area_id'           => 1, 
                'usuario_id'        =>  $laura->id, // asignada a Diego
                'titulo'            => 'Diseñar landing de producto',
                'descripcion'       => '<p>Versión inicial de la landing con hero y secciones.</p>',
                'tiempo_estimado_h' => 10.5, // 10.5 horas
                'fecha_de_entrega'  => now()->addDays(5)->toDateString(),
                'posicion'          => 2,
                'archivada'         => false,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        foreach ($tareas as $t) {
            DB::table('tarea_servicios')->updateOrInsert(['id' => $t['id']], $t);
        }
    }
}