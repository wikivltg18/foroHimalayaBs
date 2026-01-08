<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Str;

class AgendaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function prepareMinimalDeps(User $user): array
    {
        // Create minimal rows needed by FK constraints
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

        return [$columnaId, $estadoTareaId];
    }

    public function test_schedule_authorization_allows_owner()
    {
        $user = User::factory()->create();
        [$columnaId, $estadoTareaId] = $this->prepareMinimalDeps($user);

        $tareaId = (string) Str::uuid();
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => null,
            'usuario_id' => $user->id, // owner
            'titulo' => 'Tarea Propia',
            'descripcion' => '',
            'tiempo_estimado_h' => 1,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->actingAs($user)->postJson('/agenda/schedule', [
            'tarea_id' => $tareaId,
            'user_id' => $user->id,
            'start' => now()->setTime(9,0)->toDateTimeString(),
        ]);

        $resp->assertStatus(200);
        $this->assertDatabaseHas('tarea_bloques', ['tarea_id' => $tareaId, 'user_id' => $user->id]);
    }

    public function test_schedule_authorization_denies_other_users()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        [$columnaId, $estadoTareaId] = $this->prepareMinimalDeps($owner);

        $tareaId = (string) Str::uuid();
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => null,
            'usuario_id' => $owner->id,
            'titulo' => 'Tarea Owner',
            'descripcion' => '',
            'tiempo_estimado_h' => 1,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->actingAs($other)->postJson('/agenda/schedule', [
            'tarea_id' => $tareaId,
            'user_id' => $other->id,
            'start' => now()->setTime(9,0)->toDateTimeString(),
        ]);

        $resp->assertStatus(403);
        $this->assertDatabaseMissing('tarea_bloques', ['tarea_id' => $tareaId, 'user_id' => $other->id]);
    }
}
