<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Services\AgendaScheduler;
use App\Models\TareaServicio;
use App\Models\TareaBloque;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AgendaSchedulerOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_skips_existing_busy_segment()
    {
        Queue::fake();

        $user = User::factory()->create();

        // Create minimal deps
        $estadoClienteId = \DB::table('estado_clientes')->insertGetId(['nombre' => 'Activo', 'created_at' => now(), 'updated_at' => now()]);
        $clienteId = \DB::table('clientes')->insertGetId([
            'logo' => null,
            'nombre' => 'Cliente Test',
            'correo_electronico' => Str::random(6) . '@example.com',
            'telefono' => null,
            'sitio_web' => null,
            'id_usuario' => $user->id,
            'id_estado_cliente' => $estadoClienteId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $modalidadId = \DB::table('modalidads')->insertGetId(['nombre' => 'Remoto', 'created_at' => now(), 'updated_at' => now()]);
        $tipoId = \DB::table('tipo_servicios')->insertGetId(['modalidad_id' => $modalidadId, 'nombre' => 'Tipo Test', 'created_at' => now(), 'updated_at' => now()]);

        $servicioId = \DB::table('servicios')->insertGetId([
            'cliente_id' => $clienteId,
            'nombre_servicio' => 'Servicio Test',
            'modalidad_id' => $modalidadId,
            'tipo_servicio_id' => $tipoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $estadoTableroId = \DB::table('estado_tablero_servicios')->insertGetId(['nombre' => 'Activo', 'created_at' => now(), 'updated_at' => now()]);

        $tableroId = (string) Str::uuid();
        \DB::table('tableros_servicio')->insert([
            'id' => $tableroId,
            'nombre_del_tablero' => 'Tablero Test',
            'servicio_id' => $servicioId,
            'cliente_id' => $clienteId,
            'estado_tablero_id' => $estadoTableroId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $columnaId = (string) Str::uuid();
        \DB::table('columnas_tablero_servicio')->insert([
            'id' => $columnaId,
            'nombre_de_la_columna' => 'Columna Test',
            'tablero_servicio_id' => $tableroId,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $estadoTareaId = \DB::table('estado_tarea')->insertGetId(['nombre' => 'Programada', 'created_at' => now(), 'updated_at' => now()]);

        $tareaId = (string) Str::uuid();
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => null,
            'usuario_id' => null,
            'titulo' => 'Test tarea',
            'descripcion' => '',
            'tiempo_estimado_h' => 2.5, // 2.5 hours
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create an existing busy block 09:00-10:00 for the user
        $today = Carbon::now()->startOfDay();
        \DB::table('tarea_bloques')->insert([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tareaId,
            'user_id' => $user->id,
            'scheduled_by' => $user->id,
            'inicio' => $today->copy()->setTime(9,0),
            'fin' => $today->copy()->setTime(10,0),
            'orden' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tarea = TareaServicio::findOrFail($tareaId);

        $sched = new AgendaScheduler();

        // Schedule should place new blocks after 10:00 (respecting existing busy segment)
        $sched->schedule($tarea, $user->id, $today->copy()->setTime(9,0), $user->id);

        $blocks = TareaBloque::where('tarea_id', $tareaId)->where('user_id', $user->id)->orderBy('inicio')->get();

        $this->assertGreaterThanOrEqual(2, $blocks->count());
        // First existing block ends at 10:00, next block should start at/after 10:00
        $this->assertTrue($blocks[1]->inicio->gte($today->copy()->setTime(10,0)));
    }
}
