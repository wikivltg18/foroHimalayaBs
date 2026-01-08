<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncTaskBlockToCalendarJob;
use App\Jobs\RemoveSingleBlockFromCalendarJob;
use App\Models\TareaBloque;
use App\Models\TareaServicio;
use App\Models\User;
use App\Models\UserGoogleAccount;
use App\Models\TaskCalendarBlockEvent;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Str;
use Mockery;
use App\Jobs\CreateTaskCalendarEvent;
use App\Jobs\RemoveTaskCalendarEvent;
use App\Jobs\RemoveTaskBlocksFromCalendarJob;

class CalendarSyncJobsTest extends TestCase
{
    use RefreshDatabase;

    protected function createMinimalTask(User $user): TareaServicio
    {
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
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'titulo' => 'Tarea Test',
            'descripcion' => 'Descripción',
            'tiempo_estimado_h' => 2,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return TareaServicio::find($tareaId);
    }

    public function test_sync_job_creates_event_when_google_account_exists()
    {
        $user = User::factory()->create();
        $tarea = $this->createMinimalTask($user);

        // Create Google account
        $account = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token', 'expires_in' => 3600]),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
            'calendar_id' => 'primary',
        ]);

        // Create block
        $block = TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $user->id,
            'scheduled_by' => $user->id,
            'inicio' => now()->setTime(9, 0),
            'fin' => now()->setTime(10, 0),
            'orden' => 1,
        ]);

        // Mock GoogleCalendarService
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('createEvent')
                ->once()
                ->andReturn('google-event-id-123');
        });

        $job = new SyncTaskBlockToCalendarJob($block->id);
        $job->handle(app(GoogleCalendarService::class));

        // Verify event was saved
        $this->assertDatabaseHas('task_calendar_block_events', [
            'tarea_bloque_id' => $block->id,
            'google_event_id' => 'google-event-id-123',
        ]);
    }

    public function test_sync_job_updates_existing_event()
    {
        $user = User::factory()->create();
        $tarea = $this->createMinimalTask($user);

        $account = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token']),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
            'calendar_id' => 'primary',
        ]);

        $block = TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $user->id,
            'scheduled_by' => $user->id,
            'inicio' => now()->setTime(9, 0),
            'fin' => now()->setTime(10, 0),
            'orden' => 1,
        ]);

        // Create existing calendar event
        TaskCalendarBlockEvent::create([
            'id' => (string) Str::uuid(),
            'tarea_bloque_id' => $block->id,
            'user_id' => $user->id,
            'calendar_id' => 'primary',
            'google_event_id' => 'existing-event-id',
        ]);

        // Mock update
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('updateEvent')
                ->once()
                ->with(Mockery::any(), 'existing-event-id', Mockery::any());
                
            $mock->shouldNotReceive('createEvent');
        });

        $job = new SyncTaskBlockToCalendarJob($block->id);
        $job->handle(app(GoogleCalendarService::class));
    }

    public function test_sync_job_skips_when_no_google_account()
    {
        $user = User::factory()->create();
        $tarea = $this->createMinimalTask($user);

        $block = TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $user->id,
            'scheduled_by' => $user->id,
            'inicio' => now()->setTime(9, 0),
            'fin' => now()->setTime(10, 0),
            'orden' => 1,
        ]);

        // No Google account = should skip gracefully
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldNotReceive('createEvent');
            $mock->shouldNotReceive('updateEvent');
        });

        $job = new SyncTaskBlockToCalendarJob($block->id);
        $job->handle(app(GoogleCalendarService::class));

        // Should not create calendar event
        $this->assertDatabaseMissing('task_calendar_block_events', [
            'tarea_bloque_id' => $block->id,
        ]);
    }

    public function test_remove_job_deletes_event_from_google()
    {
        $user = User::factory()->create();
        $tarea = $this->createMinimalTask($user);

        $account = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token']),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $block = TareaBloque::create([
            'id' => (string) Str::uuid(),
            'tarea_id' => $tarea->id,
            'user_id' => $user->id,
            'scheduled_by' => $user->id,
            'inicio' => now()->setTime(9, 0),
            'fin' => now()->setTime(10, 0),
            'orden' => 1,
        ]);

        $calEvent = TaskCalendarBlockEvent::create([
            'id' => (string) Str::uuid(),
            'tarea_bloque_id' => $block->id,
            'user_id' => $user->id,
            'calendar_id' => 'primary',
            'google_event_id' => 'event-to-delete',
        ]);

        // Mock delete
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('deleteEvent')
                ->once()
                ->with(Mockery::any(), 'event-to-delete');
        });

        $job = new RemoveSingleBlockFromCalendarJob($block->id);
        $job->handle(app(GoogleCalendarService::class));

        // Verify local record is deleted
        $this->assertDatabaseMissing('task_calendar_block_events', [
            'id' => $calEvent->id,
        ]);
    }

    public function test_remove_job_handles_missing_block_gracefully()
    {
        $fakeBlockId = (string) Str::uuid();

        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldNotReceive('deleteEvent');
        });

        $job = new RemoveSingleBlockFromCalendarJob($fakeBlockId);
        $job->handle(app(GoogleCalendarService::class));

        // Should not throw exception
        $this->assertTrue(true);
    }

    public function test_store_dispatches_create_task_calendar_event_when_fecha_de_entrega_set()
    {
        Queue::fake();

        $user = User::factory()->create();

        // Crear filas mínimas dependientes
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

        // Mapa y área contratada
        $mapaId = \DB::table('mapa_del_cliente')->insertGetId(['servicio_id' => $servicioId, 'created_at' => now(), 'updated_at' => now()]);
        $areaId = \DB::table('areas')->insertGetId(['nombre' => 'Área Test', 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('mapa_areas')->insert([
            'mapa_del_cliente_id' => $mapaId,
            'area_id' => $areaId,
            'horas_contratadas' => 1,
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

        $payload = [
            'titulo' => 'Tarea con fecha',
            'estado_id' => $estadoTareaId,
            'area_id' => $areaId,
            'usuario_id' => $user->id,
            'descripcion' => 'Descripción',
            'tiempo_estimado_h' => 1,
            'fecha_de_entrega' => now()->addDay()->toDateTimeString(),
        ];

        $response = $this->actingAs($user)->post("/clientes/{$clienteId}/servicios/{$servicioId}/tableros/{$tableroId}/columnas/{$columnaId}/tareas", $payload);
        $response->assertStatus(302);

        Queue::assertPushed(CreateTaskCalendarEvent::class, function ($job) use ($user) {
            return $job->userId === $user->id;
        });

        Queue::assertPushedOn('calendar', CreateTaskCalendarEvent::class);
    }

    public function test_update_dispatches_remove_task_calendar_event_when_fecha_removed()
    {
        Queue::fake();

        $user = User::factory()->create();

        // Crear filas mínimas dependientes (similar setup)
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

        $mapaId = \DB::table('mapa_del_cliente')->insertGetId(['servicio_id' => $servicioId, 'created_at' => now(), 'updated_at' => now()]);
        $areaId = \DB::table('areas')->insertGetId(['nombre' => 'Área Test', 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('mapa_areas')->insert([
            'mapa_del_cliente_id' => $mapaId,
            'area_id' => $areaId,
            'horas_contratadas' => 1,
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

        // Crear tarea con fecha inicial
        $tareaId = (string) Str::uuid();
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => $areaId,
            'usuario_id' => $user->id,
            'titulo' => 'Tarea Test',
            'descripcion' => 'Descripción',
            'tiempo_estimado_h' => 2,
            'fecha_de_entrega' => now()->addDay()->toDateTimeString(),
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->put("/clientes/{$clienteId}/servicios/{$servicioId}/tableros/{$tableroId}/columnas/{$columnaId}/tareas/{$tareaId}", [
            'titulo' => 'Tarea Test',
            'estado_id' => $estadoTareaId,
            'area_id' => $areaId,
            'usuario_id' => $user->id,
            'descripcion' => 'Descripción actualización',
            'tiempo_estimado_h' => 2,
            'fecha_de_entrega' => null,
        ]);

        $response->assertStatus(302);

        Queue::assertPushed(RemoveTaskCalendarEvent::class, function ($job) use ($tareaId) {
            return $job->tareaId === $tareaId && $job->userId === null;
        });
    }

    public function test_destroy_dispatches_remove_jobs_for_task()
    {
        Queue::fake();

        $user = User::factory()->create();

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

        $mapaId = \DB::table('mapa_del_cliente')->insertGetId(['servicio_id' => $servicioId, 'created_at' => now(), 'updated_at' => now()]);
        $areaId = \DB::table('areas')->insertGetId(['nombre' => 'Área Test', 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('mapa_areas')->insert([
            'mapa_del_cliente_id' => $mapaId,
            'area_id' => $areaId,
            'horas_contratadas' => 1,
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
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => $areaId,
            'usuario_id' => $user->id,
            'titulo' => 'Tarea Test',
            'descripcion' => 'Descripción',
            'tiempo_estimado_h' => 2,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete("/clientes/{$clienteId}/servicios/{$servicioId}/tableros/{$tableroId}/columnas/{$columnaId}/tareas/{$tareaId}");
        $response->assertStatus(302);

        Queue::assertPushed(RemoveTaskBlocksFromCalendarJob::class, function ($job) use ($tareaId) {
            return $job->tareaId === $tareaId;
        });

        Queue::assertPushed(RemoveTaskCalendarEvent::class, function ($job) use ($tareaId) {
            return $job->tareaId === $tareaId;
        });
    }

    public function test_manual_create_endpoint_dispatches_create_job()
    {
        Queue::fake();

        $user = User::factory()->create();

        // Minimal deps and create a task assigned to user
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

        $mapaId = \DB::table('mapa_del_cliente')->insertGetId(['servicio_id' => $servicioId, 'created_at' => now(), 'updated_at' => now()]);
        $areaId = \DB::table('areas')->insertGetId(['nombre' => 'Área Test', 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('mapa_areas')->insert([
            'mapa_del_cliente_id' => $mapaId,
            'area_id' => $areaId,
            'horas_contratadas' => 1,
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
        \DB::table('tarea_servicios')->insert([
            'id' => $tareaId,
            'columna_id' => $columnaId,
            'estado_id' => $estadoTareaId,
            'area_id' => $areaId,
            'usuario_id' => $user->id,
            'titulo' => 'Tarea Test',
            'descripcion' => 'Descripción',
            'tiempo_estimado_h' => 2,
            'posicion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verificar que la tarea existe y está asignada al usuario (debug en test)
        $taskModel = TareaServicio::find($tareaId);
        $this->assertNotNull($taskModel);
        $this->assertEquals($user->id, $taskModel->usuario_id);
        // Verificar tipos y comparador estricto
        $this->assertSame($user->id, $taskModel->usuario_id);

        // For testing, ensure the 'schedule-task' Gate allows this user
        \Gate::define('schedule-task', function ($u, $t) { return true; });

        $response = $this->actingAs($user)->post("/tareas/{$tareaId}/calendar/create");
        $response->assertStatus(302);

        Queue::assertPushed(CreateTaskCalendarEvent::class, function ($job) use ($user) {
            return $job->userId === $user->id;
        });

        Queue::assertPushedOn('calendar', CreateTaskCalendarEvent::class);
    }

}

