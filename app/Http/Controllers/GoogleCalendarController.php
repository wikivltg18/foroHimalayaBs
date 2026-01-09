<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\UserGoogleAccount;
use App\Models\TaskCalendarEvent;
use App\Services\GoogleCalendarService;

class GoogleCalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $gcal) {}

    private function account(): ?UserGoogleAccount
    {
        return UserGoogleAccount::where('user_id', auth()->id())->first();
    }

    /** Muestra lista de calendarios y permite seleccionar uno por defecto */
    public function listCalendars()
    {
        $acc = $this->account();
        if (!$acc) return redirect()->route('google.redirect')->with('info', 'Conecta tu cuenta de Google primero.');

        $calendars = $this->gcal->listCalendars($acc);

        return view('google.calendars', [
            'calendars' => $calendars,
            'selected'  => $acc->calendar_id ?? 'primary',
        ]);
    }

    /** Guarda el calendario por defecto */
    public function setDefaultCalendar(Request $r)
    {
        $r->validate(['calendar_id' => 'required|string']);
        $acc = $this->account();
        if (!$acc) return back()->withErrors('Conecta tu cuenta primero.');
        $acc->update(['calendar_id' => $r->calendar_id]);

        return back()->with('success', 'Calendario por defecto guardado.');
    }

    /** Crea un evento de prueba de 30 minutos desde ahora */
    public function createTestEvent(Request $r)
    {
        $acc = $this->account();
        if (!$acc) return redirect()->route('google.redirect')->with('info', 'Conecta tu cuenta de Google primero.');

        $start = now()->addMinutes(5);
        $end   = (clone $start)->addMinutes(30);

        try {
            $eventId = $this->gcal->createEvent($acc, [
                'summary'     => 'Evento de prueba',
                'description' => 'http://localhost:8000/google/calendars',
                'start'       => $start,
                'end'         => $end,
                // 'attendees' => [['email' => 'alguien@dominio.com']],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creando evento de prueba en Google Calendar: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors('Error creando evento de prueba en Google Calendar. Revisa los logs para más detalles.');
        }

        // Evento de prueba: no persistimos en DB para evitar violaciones de FK
        return back()->with('success', "Evento creado en Google Calendar (ID: {$eventId}). (No se guardó en BD porque es un evento de prueba)");
    }

    /**
     * Crear un evento de calendario para una tarea específica (manual).
     * Permiso: 'schedule-task' o el usuario asignado a la tarea.
     */
    public function createEventForTask(Request $r, \App\Models\TareaServicio $tarea)
    {
        if (!\Gate::allows('schedule-task', $tarea)) {
            abort(403);
        }

        if (!$tarea->usuario_id) {
            return back()->with('error', 'La tarea no tiene un colaborador asignado.');
        }

        // Despacha el job para crear (el job validará si el usuario tiene cuenta Google conectada)
        dispatch(new \App\Jobs\CreateTaskCalendarEvent($tarea->id, (int) $tarea->usuario_id))->onQueue('calendar');

        return back()->with('success', 'Solicitud enviada: se creará el evento en Google Calendar del colaborador asignado.');
    }

    /**
     * Obtener calendarios del usuario autenticado (JSON)
     * Retorna: [{ id, summary, primary, calendar_id (default) }]
     */
    public function getUserCalendars()
    {
        $acc = $this->account();
        if (!$acc) {
            return response()->json([
                'connected' => false,
                'message'   => 'Cuenta de Google no conectada',
                'calendars' => [],
            ]);
        }

        try {
            $calendars = $this->gcal->listCalendars($acc);
            
            return response()->json([
                'connected'       => true,
                'default_calendar' => $acc->calendar_id ?? 'primary',
                'calendars'       => $calendars,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo calendarios del usuario', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'connected' => true,
                'message'   => 'Error al obtener calendarios',
                'calendars' => [],
            ], 500);
        }
    }
}