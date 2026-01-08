<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TareaBloquesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tz = config('app.timezone', 'America/Bogota');
        $today = Carbon::now($tz)->startOfDay();

        $carla = DB::table('users')->where('email', 'carlos06182016@gmail.com')->first();
        $diego = DB::table('users')->where('email', 'laura.alejandra12@gmail.com')->first();

        if (!$carla || !$diego) return;

        $tareaId1 = '0bcdf056-66e8-4519-b922-83c6be3d4c4c';
        $tareaId2 = '40dd197e-89c9-4845-9da3-676f3869b9e8';

        // Bloques: Carla hoy 09:00-11:00, 14:00-16:00
        $bloques = [
            [
                'id'           => (string) Str::uuid(),
                'tarea_id'     => $tareaId1,
                'user_id'      => $carla->id,
                'scheduled_by' => $carla->id, // o un gestor
                'inicio'       => $today->copy()->setTime(9,0),
                'fin'          => $today->copy()->setTime(11,0),
                'orden'        => 1,
            ],
            [
                'id'           => (string) Str::uuid(),
                'tarea_id'     => $tareaId1,
                'user_id'      => $carla->id,
                'scheduled_by' => $carla->id,
                'inicio'       => $today->copy()->setTime(14,0),
                'fin'          => $today->copy()->setTime(16,0),
                'orden'        => 2,
            ],
            // Bloques: Diego hoy 10:00-12:00, 15:00-17:30
            [
                'id'           => (string) Str::uuid(),
                'tarea_id'     => $tareaId2,
                'user_id'      => $diego->id,
                'scheduled_by' => $diego->id,
                'inicio'       => $today->copy()->setTime(10,0),
                'fin'          => $today->copy()->setTime(12,0),
                'orden'        => 1,
            ],
            [
                'id'           => (string) Str::uuid(),
                'tarea_id'     => $tareaId2,
                'user_id'      => $diego->id,
                'scheduled_by' => $diego->id,
                'inicio'       => $today->copy()->setTime(15,0),
                'fin'          => $today->copy()->setTime(17,30),
                'orden'        => 2,
            ],
        ];

        foreach ($bloques as $b) {
            DB::table('tarea_bloques')->updateOrInsert(
                ['id' => $b['id']],
                [
                    'tarea_id'     => $b['tarea_id'],
                    'user_id'      => $b['user_id'],
                    'scheduled_by' => $b['scheduled_by'],
                    'inicio'       => $b['inicio'],
                    'fin'          => $b['fin'],
                    'orden'        => $b['orden'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }

        // Actualiza ventana global de las dos tareas
        foreach ([$tareaId1, $tareaId2] as $tid) {
            $min = DB::table('tarea_bloques')->where('tarea_id', $tid)->min('inicio');
            $max = DB::table('tarea_bloques')->where('tarea_id', $tid)->max('fin');
            DB::table('tarea_servicios')->where('id', $tid)->update([
                'programada_inicio' => $min,
                'programada_fin'    => $max,
                'updated_at'        => now(),
            ]);
        }
    }
}