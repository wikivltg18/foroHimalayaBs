<?php

namespace App\Jobs;

use App\Models\TareaBloque;
use App\Models\TaskCalendarBlockEvent;
use App\Models\UserGoogleAccount;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RemoveSingleBlockFromCalendarJob implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $backoff = [30, 120]; // Reintentos: 30s, 2min

    public function __construct(public string $tareaBloqueId) {}

    public function handle(GoogleCalendarService $gcal): void
    {
        $block = TareaBloque::with('gcal')->find($this->tareaBloqueId);
        if (!$block) {
            Log::warning("RemoveSingleBlockFromCalendarJob: Bloque no encontrado", ['id' => $this->tareaBloqueId]);
            return;
        }

        $g = $block->gcal;
        if (!$g) {
            Log::info("RemoveSingleBlockFromCalendarJob: Bloque sin evento de Google asociado", ['bloque_id' => $this->tareaBloqueId]);
            return;
        }

        Log::info("RemoveSingleBlockFromCalendarJob: Eliminando evento de Google Calendar", [
            'bloque_id' => $this->tareaBloqueId,
            'google_event_id' => $g->google_event_id
        ]);

        $acc = UserGoogleAccount::where('user_id', $block->user_id)->first();
        if ($acc && $acc->refresh_token) {
            try {
                $gcal->deleteEvent($acc, $g->google_event_id);
                Log::info("RemoveSingleBlockFromCalendarJob: Evento eliminado exitosamente", [
                    'google_event_id' => $g->google_event_id
                ]);
            } catch (\Google_Service_Exception $e) {
                // Si el evento ya no existe en Google (404), no es un error crítico
                if ($e->getCode() === 404 || $e->getCode() === 410) {
                    Log::info("RemoveSingleBlockFromCalendarJob: Evento ya no existe en Google Calendar", [
                        'google_event_id' => $g->google_event_id,
                        'code' => $e->getCode()
                    ]);
                } else {
                    Log::error("RemoveSingleBlockFromCalendarJob: Error de Google API", [
                        'google_event_id' => $g->google_event_id,
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                    // No lanzamos excepción para evitar fallos en borrados
                }
            } catch (\Throwable $e) {
                Log::error("RemoveSingleBlockFromCalendarJob: Error inesperado", [
                    'google_event_id' => $g->google_event_id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::warning("RemoveSingleBlockFromCalendarJob: Usuario sin cuenta Google o refresh_token", [
                'user_id' => $block->user_id,
                'bloque_id' => $this->tareaBloqueId
            ]);
        }

        // Eliminar el registro local independientemente del resultado con Google
        $g->delete();
        Log::info("RemoveSingleBlockFromCalendarJob: Registro local eliminado", ['bloque_id' => $this->tareaBloqueId]);
    }
}