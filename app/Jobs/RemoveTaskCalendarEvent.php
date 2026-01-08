<?php

namespace App\Jobs;

use App\Models\TaskCalendarEvent;
use App\Models\UserGoogleAccount;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RemoveTaskCalendarEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $tareaId, public ?int $userId = null) {}

    public function handle(GoogleCalendarService $gcal): void
    {
        $rows = TaskCalendarEvent::where('tarea_id', $this->tareaId)
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->get();

        foreach ($rows as $r) {
            $acc = UserGoogleAccount::where('user_id', $r->user_id)->first();
            if ($acc && $acc->refresh_token) {
                try {
                    $gcal->deleteEvent($acc, $r->google_event_id);
                    Log::info("RemoveTaskCalendarEvent: Evento eliminado en Google", ['tarea_id' => $this->tareaId, 'google_event_id' => $r->google_event_id]);
                } catch (\Google_Service_Exception $e) {
                    if ($e->getCode() === 404 || $e->getCode() === 410) {
                        Log::info("RemoveTaskCalendarEvent: Evento ya no existe en Google Calendar", ['google_event_id' => $r->google_event_id, 'code' => $e->getCode()]);
                    } else {
                        Log::error("RemoveTaskCalendarEvent: Error de Google API", ['error' => $e->getMessage(), 'code' => $e->getCode()]);
                    }
                } catch (\Throwable $e) {
                    Log::error("RemoveTaskCalendarEvent: Error inesperado", ['error' => $e->getMessage()]);
                }
            } else {
                Log::warning("RemoveTaskCalendarEvent: Usuario sin cuenta Google o refresh_token", ['user_id' => $r->user_id, 'tarea_id' => $this->tareaId]);
            }

            // Eliminar registro local
            $r->delete();
        }
    }
}
