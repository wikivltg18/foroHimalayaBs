<?php

namespace App\Jobs;

use App\Models\{TaskCalendarBlockEvent, UserGoogleAccount};
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RemoveSingleBlockFromCalendarJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $bloqueId) {}

    public function handle(GoogleCalendarService $gcal): void
    {
        $events = TaskCalendarBlockEvent::where('tarea_bloque_id', $this->bloqueId)->get();

        foreach ($events as $event) {
            $acc = UserGoogleAccount::where('user_id', $event->user_id)->first();
            
            if ($acc && $acc->refresh_token) {
                try {
                    $gcal->deleteEvent($acc, $event->google_event_id, $event->calendar_id);
                    Log::info("Removed block event from Google Calendar", [
                        'block_id' => $this->bloqueId,
                        'google_event_id' => $event->google_event_id
                    ]);
                } catch (\Google_Service_Exception $e) {
                    if ($e->getCode() === 404 || $e->getCode() === 410) {
                        Log::info("Block event already removed from Google Calendar", [
                            'google_event_id' => $event->google_event_id
                        ]);
                    } else {
                        Log::error("Error removing block event from Google Calendar", [
                            'error' => $e->getMessage(),
                            'code' => $e->getCode()
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Unexpected error removing block event", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Eliminar registro local
            $event->delete();
        }
    }
}