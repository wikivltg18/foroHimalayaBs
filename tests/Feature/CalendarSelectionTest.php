<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserGoogleAccount;
use App\Models\TareaServicio;
use App\Services\GoogleCalendarService;
use Mockery;
use Illuminate\Support\Str;

class CalendarSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_list_calendars_returns_calendars_and_email()
    {
        $user = User::factory()->create();
        $acc = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => '123',
            'email' => 'test@example.com',
            'access_token' => json_encode(['access_token' => 'fake']),
            'refresh_token' => 'fake',
            'token_expires_at' => now()->addHour(),
        ]);

        $mockService = Mockery::mock(GoogleCalendarService::class);
        $mockService->shouldReceive('listCalendars')
            ->once()
            ->andReturn([
                ['id' => 'cal1', 'summary' => 'test@example.com', 'primary' => true],
                ['id' => 'cal2', 'summary' => 'Other', 'primary' => false],
            ]);

        $this->app->instance(GoogleCalendarService::class, $mockService);

        $response = $this->actingAs($user)
             ->getJson(route('ajax.users.google-calendars', $user->id));

        $response->assertStatus(200)
            ->assertJson([
                'email' => $user->email,
                'calendars' => [
                    ['id' => 'cal1', 'summary' => 'test@example.com'],
                    ['id' => 'cal2', 'summary' => 'Other'],
                ]
            ]);
    }

    public function test_create_task_calendar_event_uses_stored_id()
    {
        $user = User::factory()->create();
        $acc = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => '1234',
            'email' => 'test@example.com',
            'access_token' => json_encode(['access_token' => 'fake']),
        ]);

        // Create manually since Factories don't exist
        $estadoCliente = \App\Models\EstadoCliente::create(['nombre' => 'Activo']);
        $cliente = \App\Models\Cliente::create([
            'nombre' => 'Test Client', 
            'correo_electronico' => 'client@test.com',
            'id_usuario' => $user->id,
            'id_estado_cliente' => $estadoCliente->id
        ]);

        $modalidad = \App\Models\Modalidad::create(['nombre' => 'Presencial']);
        $tipoServicio = \App\Models\TipoServicio::create(['nombre' => 'Consultoría', 'modalidad_id' => $modalidad->id]);

        $servicio = \App\Models\Servicio::create([
            'nombre_servicio' => 'Test Service', 
            'cliente_id' => $cliente->id,
            'modalidad_id' => $modalidad->id,
            'tipo_servicio_id' => $tipoServicio->id,
        ]);

        $estadoTablero = \App\Models\EstadoTableroServicio::create(['nombre' => 'Activo']);

        $tablero = \App\Models\TableroServicio::create([
            'id' => (string) Str::uuid(),
            'nombre_del_tablero' => 'Test Board', 
            'servicio_id' => $servicio->id, 
            'cliente_id' => $cliente->id,
            'estado_tablero_id' => $estadoTablero->id,
            // redundant fields if not auto-filled
            'nombre_del_servicio' => 'Test Service',
            'nombre_cliente' => 'Test Client',
            'nombre_modalidad' => 'Presencial',
            'nombre_tipo_de_servicio' => 'Consultoria'
        ]);

        $columna = \App\Models\ColumnaTableroServicio::create([
            'nombre_de_la_columna' => 'Test Col', 
            'tablero_servicio_id' => $tablero->id, // Note: Tablero has `hasMany(ColumnaTableroServicio::class, 'tablero_servicio_id')`
            // But Columna migration might say `tablero_id`? 
            // Blade view confusing: $columna->tablero is relation.
            // TableroServicio.php: return $this->hasMany(ColumnaTableroServicio::class, 'tablero_servicio_id');
            // So column name is `tablero_servicio_id`.
            'posicion' => 1
        ]);
        $area = \App\Models\Area::create(['nombre' => 'Test Area']);
        $estado = \App\Models\EstadoTarea::create(['nombre' => 'Pendiente']);

        $tarea = new TareaServicio();
        $tarea->id = (string) Str::uuid();
        $tarea->titulo = 'Test';
        $tarea->columna_id = $columna->id;
        $tarea->estado_id = $estado->id;
        $tarea->area_id = $area->id;
        $tarea->usuario_id = $user->id;
        $tarea->google_calendar_id = 'custom-cal-id';
        $tarea->descripcion = 'desc';
        $tarea->tiempo_estimado_h = 1;
        $tarea->archivada = false;
        
        $tarea->save();

        $mockService = Mockery::mock(GoogleCalendarService::class);
        $mockService->shouldReceive('createEvent')
            ->once()
            ->with(
                Mockery::on(function($argAcc) use ($acc) { return $argAcc->id === $acc->id; }),
                Mockery::on(function($payload) {
                    return $payload['calendar_id'] === 'custom-cal-id';
                })
            )
            ->andReturn('google-evt-id');

        $job = new \App\Jobs\CreateTaskCalendarEvent($tarea->id, $user->id);
        $job->handle($mockService);

        $this->assertDatabaseHas('task_calendar_events', [
            'tarea_id' => $tarea->id,
            'calendar_id' => 'custom-cal-id',
            'google_event_id' => 'google-evt-id',
        ]);
    }
}
