<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TareaServicio;
use App\Jobs\CreateTaskCalendarEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VerifySyncTest extends TestCase
{
   // use DatabaseTransactions; // Commented out to potentially avoid issues if transaction handling is strict, but safer to use it. Let's try without if simple. Actually safer WITH it.
   // If the user's DB doesn't support nested transactions well, it might be an issue, but usually fine.
   // Let's use it to clean up.
   use DatabaseTransactions;

    public function test_creating_task_dispatches_google_calendar_job()
    {
        Bus::fake();

        $user = User::factory()->create();

        $tarea = TareaServicio::create([
            'titulo' => 'Tarea de Prueba Sync ' . rand(1000,9999),
            'usuario_id' => $user->id,
            'fecha_de_entrega' => now()->addDays(2),
            // Add other required fields if any. checking TareaServicio model...
            // It has 'id' as uuid? keyType string. valid.
        ]);

        Bus::assertDispatched(CreateTaskCalendarEvent::class, function ($job) use ($tarea) {
            return $job->tareaId === $tarea->id;
        });
    }
}
