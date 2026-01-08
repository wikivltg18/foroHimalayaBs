<?php

namespace App\Jobs;

use App\Models\TareaBloque;
use App\Models\TaskCalendarBlockEvent;
use App\Models\UserGoogleAccount;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncTaskBlockToCalendarJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 120, 300]; // Reintentos: 1min, 2min, 5min

    public function __construct(public string $tareaBloqueId) {}

    public function handle(GoogleCalendarService $gcal): void
    {
        $block = TareaBloque::with(['tarea','user','gcal'])->find($this->tareaBloqueId);
        if (!$block) {
            Log::warning("SyncTaskBlockToCalendarJob: Bloque no encontrado", ['id' => $this->tareaBloqueId]);
            return;
        }

        // Por defecto: cuenta del COLABORADOR (organizer = colaborador)
        $acc = UserGoogleAccount::where('user_id', $block->user_id)->first();

        if (!$acc || !$acc->refresh_token) {
            Log::info("SyncTaskBlockToCalendarJob: Usuario sin cuenta Google conectada", [
                'user_id' => $block->user_id,
                'bloque_id' => $this->tareaBloqueId
            ]);
            return;
        }

        try {
            $summary = $block->tarea->titulo;
            $description = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$block->tarea->descripcion)));

            if ($block->gcal) {
                // Actualizar evento existente
                Log::info("SyncTaskBlockToCalendarJob: Actualizando evento", [
                    'google_event_id' => $block->gcal->google_event_id,
                    'bloque_id' => $this->tareaBloqueId
                ]);

                $gcal->updateEvent($acc, $block->gcal->google_event_id, [
                    'summary'     => $summary,
                    'description' => $description ?: null,
                    'start'       => $block->inicio,
                    'end'         => $block->fin,
                ]);
            } else {
                // Crear nuevo evento
                Log::info("SyncTaskBlockToCalendarJob: Creando nuevo evento", [
                    'tarea_id' => $block->tarea_id,
                    'bloque_id' => $this->tareaBloqueId
                ]);

                $eventId = $gcal->createEvent($acc, [
                    'summary'     => $summary,
                    'description' => $description ?: null,
                    'start'       => $block->inicio,
                    'end'         => $block->fin,
                ]);

                TaskCalendarBlockEvent::create([
                    'id'              => (string) Str::uuid(),
                    'tarea_bloque_id' => $block->id,
                    'user_id'         => $block->user_id,
                    'calendar_id'     => $acc->calendar_id ?: 'primary',
                    'google_event_id' => $eventId,
                ]);

                Log::info("SyncTaskBlockToCalendarJob: Evento creado exitosamente", [
                    'google_event_id' => $eventId,
                    'bloque_id' => $this->tareaBloqueId
                ]);
            }
        } catch (\Google_Service_Exception $e) {
            Log::error("SyncTaskBlockToCalendarJob: Error de Google API", [
                'bloque_id' => $this->tareaBloqueId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'errors' => $e->getErrors()
            ]);
            throw $e; // Reintentará automáticamente
        } catch (\Throwable $e) {
            Log::error("SyncTaskBlockToCalendarJob: Error inesperado", [
                'bloque_id' => $this->tareaBloqueId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SyncTaskBlockToCalendarJob: Job falló después de todos los reintentos", [
            'bloque_id' => $this->tareaBloqueId,
            'error' => $exception->getMessage()
        ]);
        
        // Aquí podrías notificar al usuario o administrador
        // Notification::send($admin, new CalendarSyncFailedNotification($this->tareaBloqueId));
    }
}