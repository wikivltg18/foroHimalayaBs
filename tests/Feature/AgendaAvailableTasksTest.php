<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\TareaServicio;
use Illuminate\Support\Str;

class AgendaAvailableTasksTest extends TestCase
{
    use RefreshDatabase;

    protected function createMinimalTask(User $user, array $attributes = []): string
    {
        // Create minimal dependencies
        $estadoClienteId = \DB::table('estado_clientes')->insertGetId(['nombre' => 'Activo', 'created_at' => now(), 'updated_at' => now()]);
        $clienteId = \DB::table('clientes')->insertGetId([
            'nombre' => 'Cliente Test',
            'correo_electronico' => Str::random(6) . '@example.com',
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

        $estadoTareaId = \DB::table('estado_tarea')->insertGetId(['nombre' => 'Pendiente', 'created_at' => now(), 'updated_at' => now()]);

        $tareaId = (string) Str::uuid();
        \DB::table('tarea_servicios')->insert(array_merge([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'titulo' => 'Tarea Test',
            'descripcion' => 'Descripción test',
            'tiempo_estimado_h' => 2,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return $tareaId;
    }

    public function test_available_tasks_requires_authentication()
    {
        $response = $this->getJson('/agenda/available-tasks');
        $response->assertStatus(401);
    }

    public function test_returns_available_tasks()
    {
        $user = User::factory()->create();
        
        // Create 3 tasks
        $this->createMinimalTask($user, ['titulo' => 'Task 1']);
        $this->createMinimalTask($user, ['titulo' => 'Task 2']);
        $this->createMinimalTask($user, ['titulo' => 'Task 3']);

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => ['id', 'titulo', 'area', 'usuario', 'cliente', 'tiempo_estimado_h', 'programada']
        ]);
    }

    public function test_filters_archived_tasks()
    {
        $user = User::factory()->create();
        
        $this->createMinimalTask($user, ['titulo' => 'Active Task']);
        $this->createMinimalTask($user, ['titulo' => 'Archived Task', 'archivada' => true]);

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $data = $response->json();
        $this->assertEquals('Active Task', $data[0]['titulo']);
    }

    public function test_filters_finished_tasks()
    {
        $user = User::factory()->create();
        
        $this->createMinimalTask($user, ['titulo' => 'Pending Task']);
        $this->createMinimalTask($user, ['titulo' => 'Finished Task', 'finalizada_at' => now()]);

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $data = $response->json();
        $this->assertEquals('Pending Task', $data[0]['titulo']);
    }

    public function test_search_by_title()
    {
        $user = User::factory()->create();
        
        $this->createMinimalTask($user, ['titulo' => 'Desarrollo Frontend']);
        $this->createMinimalTask($user, ['titulo' => 'Desarrollo Backend']);
        $this->createMinimalTask($user, ['titulo' => 'Diseño UI']);

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks?search=Frontend');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $data = $response->json();
        $this->assertEquals('Desarrollo Frontend', $data[0]['titulo']);
    }

    public function test_limits_to_50_tasks()
    {
        $user = User::factory()->create();
        
        // Create 60 tasks
        for ($i = 1; $i <= 60; $i++) {
            $this->createMinimalTask($user, ['titulo' => "Task $i"]);
        }

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(50);
    }

    public function test_shows_programada_status()
    {
        $user = User::factory()->create();
        
        $this->createMinimalTask($user, [
            'titulo' => 'Scheduled Task',
            'programada_inicio' => now(),
            'programada_fin' => now()->addHours(2),
        ]);
        $this->createMinimalTask($user, ['titulo' => 'Unscheduled Task']);

        $response = $this->actingAs($user)->getJson('/agenda/available-tasks');

        $response->assertStatus(200);
        $data = $response->json();
        
        $scheduled = collect($data)->firstWhere('titulo', 'Scheduled Task');
        $unscheduled = collect($data)->firstWhere('titulo', 'Unscheduled Task');
        
        $this->assertTrue($scheduled['programada']);
        $this->assertFalse($unscheduled['programada']);
    }
}
