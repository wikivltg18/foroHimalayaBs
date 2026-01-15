<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\UserGoogleAccount;
use App\Models\TaskCalendarEvent;
use App\Services\GoogleCalendarService;
use App\Models\User;
use App\Models\TareaServicio;
use App\Models\TareaBloque;
use Carbon\Carbon;

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
        
        // Superadmin logic: list users to assign calendars
        $users = [];
        if (auth()->user()->hasRole('Superadministrador')) {
            $users = User::with('googleAccount')->orderBy('name')->get();
        }

        return view('google.calendars', [
            'calendars' => $calendars,
            'selected'  => $acc->calendar_id ?? 'primary',
            'users'     => $users, // Pass users to view
        ]);
    }

    /** Guarda el calendario por defecto */
    public function setDefaultCalendar(Request $r)
    {
        $r->validate([
            'calendar_id' => 'required|string',
            'user_id'     => 'nullable|integer|exists:users,id'
        ]);

        // Si es Superadmin y se envía user_id, actualizamos al usuario objetivo
        if ($r->filled('user_id') && auth()->user()->hasRole('Superadministrador')) {
            $targetUser = User::findOrFail($r->user_id);
            
            // Buscar o crear cuenta google placeholder
            $acc = UserGoogleAccount::firstOrNew(['user_id' => $targetUser->id]);
            
            if (!$acc->exists) {
                // Valores dummy para cumplir constraints de la tabla
                $acc->google_user_id = 'delegated_' . $targetUser->id;
                $acc->email          = $targetUser->email;
                $acc->access_token   = 'SUB_ACCOUNT'; // Marcador especial
                $acc->refresh_token  = null;
                $acc->save();
            }
            
            $acc->update(['calendar_id' => $r->calendar_id]);
            return back()->with('success', "Calendario asignado a {$targetUser->name}.");
        }

        // Comportamiento normal (usuario actual)
        $acc = $this->account();
        if (!$acc) return back()->withErrors('Conecta tu cuenta primero.');
        $acc->update(['calendar_id' => $r->calendar_id]);

        return back()->with('success', 'Calendario por defecto guardado.');
    }

    /** Crea un evento de prueba de 30 minutos desde ahora */
    public function createTestEvent(Request $r)
    {
        $calendarId = null;
        $acc = null;

        // Lógica para Superadmin probando calendario de otro usuario
        if ($r->filled('user_id') && auth()->user()->hasRole('Superadministrador')) {
            $targetUser = User::findOrFail($r->user_id);
            $targetAcc = $targetUser->googleAccount;
            
            if (!$targetAcc) return back()->withErrors("El usuario {$targetUser->name} no tiene calendario configurado.");

            if ($targetAcc->access_token === 'SUB_ACCOUNT') {
                // Si es delegado, usamos la cuenta del Superadmin (actual) pero el calendario del usuario
                $acc = $this->account();
                if (!$acc) return back()->withErrors('Tu cuenta de Superadmin no está conectada a Google.');
                $calendarId = $targetAcc->calendar_id;
            } else {
                // Si el usuario tiene su propia cuenta conectada, usamos esa (Superadmin tiene acceso total)
                $acc = $targetAcc;
            }
        } else {
            // Prueba normal (usuario actual)
            $acc = $this->account();
            if (!$acc) return redirect()->route('google.redirect')->with('info', 'Conecta tu cuenta de Google primero.');
        }

        $start = now()->addMinutes(5);
        $end   = (clone $start)->addMinutes(30);

        try {
            $eventId = $this->gcal->createEvent($acc, [
                'summary'     => 'Evento de prueba (System)',
                'description' => 'Test de conexión desde plataforma',
                'start'       => $start,
                'end'         => $end,
            ], $calendarId);
        } catch (\Exception $e) {
            \Log::error('Error creando evento de prueba: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors('Error creando evento de prueba en Google Calendar.');
        }

        // Evento de prueba: no persistimos en DB para evitar violaciones de FK
        return back()->with('success', "Evento de prueba creado correctamente (ID: {$eventId}).");
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
     * Obtener calendarios de un usuario específico o del autenticado (JSON)
     * Retorna: [{ id, summary, primary, calendar_id (default) }]
     */
    public function getUserCalendars(Request $request)
    {
        $userId = $request->query('user_id', auth()->id());
        $currentUser = auth()->user();

        // Seguridad: si se pide otro usuario, debe ser Superadministrador
        if ($userId != $currentUser->id && !$currentUser->hasRole('Superadministrador')) {
            return response()->json([
                'connected' => false,
                'message'   => 'No tienes permiso para ver los calendarios de este usuario',
                'calendars' => [],
            ], 403);
        }

        $acc = UserGoogleAccount::where('user_id', $userId)->first();
        
        if (!$acc) {
            return response()->json([
                'connected' => false,
                'message'   => 'Cuenta de Google no conectada',
                'calendars' => [],
            ]);
        }

        try {
            // Si es Superadmin consultando una cuenta delegada, usamos la cuenta del Superadmin
            // pero el calendar_id del usuario objetivo. 
            // NOTA: listCalendars necesita una cuenta vinculada real para listar.
            // Si el colaborador NO tiene cuenta propia, listaremos los del Superadmin
            // para permitirle elegir uno para el colaborador.
            $tokenAcc = ($acc->access_token === 'SUB_ACCOUNT') ? $this->account() : $acc;
            
            if (!$tokenAcc) {
                return response()->json([
                    'connected' => false,
                    'message'   => 'No hay una cuenta de administrador vinculada para gestionar este calendario',
                    'calendars' => [],
                ]);
            }

            $calendars = $this->gcal->listCalendars($tokenAcc);
            
            return response()->json([
                'connected'        => true,
                'default_calendar' => $acc->calendar_id ?? 'primary',
                'calendars'        => $calendars,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo calendarios del usuario', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'connected' => true,
                'message'   => 'Error al obtener calendarios',
                'calendars' => [],
            ], 500);
        }
    }
    /**
     * Vista de solo lectura del calendario del equipo.
     * Muestra eventos de todos los colaboradores (reutilizando agenda.events).
     */
    public function viewTeamCalendar()
    {
        $acc = $this->account();
        if (!$acc) return redirect()->route('google.redirect')->with('info', 'Conecta tu cuenta de Google primero para ver el calendario.');

        return view('google.team_calendar');
    }

    public function resources()
    {
        return User::selectRaw('id as id, name as title')->orderBy('name')->get();
    }

    public function events(Request $r)
    {
        $from = Carbon::parse($r->query('from'));
        $to   = Carbon::parse($r->query('to'));

        \Log::info("GoogleCalendarController::events Request: From $from To $to");

        // 1. Bloques de trabajo programados
        $bloques = TareaBloque::with('tarea')
            ->where(function($q) use ($from,$to) {
                $q->whereBetween('inicio', [$from, $to])
                  ->orWhereBetween('fin',   [$from, $to])
                  ->orWhere(function($qq) use ($from,$to){
                      $qq->where('inicio','<=',$from)->where('fin','>=',$to);
                  });
            })
            ->get()
            ->filter(fn($b) => $b->tarea !== null)
            ->map(function($b){
                $tz = config('app.display_timezone');
                return [
                    'id'         => $b->id,
                    'resourceId' => $b->user_id,
                    // Enviar "Floating Time" (hora local sin zona)
                    // Como el JS está en modo UTC (fallback), si enviamos "2023...T08:00:00",
                    // FullCalendar lo pintará a las 08:00.
                    'start'      => $b->inicio->timezone($tz)->format('Y-m-d\TH:i:s'),
                    'end'        => $b->fin->timezone($tz)->format('Y-m-d\TH:i:s'),
                    'title'      => $b->tarea->titulo,
                    'extendedProps' => [
                        'type'     => 'block',
                        'tarea_id' => $b->tarea_id,
                        'orden'    => $b->orden,
                    ],
                ];
            })
            ->values();

        // 2. Tareas por fecha de entrega (Hitos/Deadlines)
        // Solo tareas con usuario asignado y fecha de entrega en el rango
        $entregas = TareaServicio::with('usuario')
            ->whereNotNull('usuario_id')
            ->whereNotNull('fecha_de_entrega')
            ->whereBetween('fecha_de_entrega', [$from, $to])
            ->get()
            ->map(function($t){
                // Representamos la entrega como un evento de 1 hora terminando en la hora de entrega, 
                // o un evento de todo el día si prefieres. 
                // Aquí: 1 hora antes de la fecha_de_entrega hasta fecha_de_entrega.
                $end   = $t->fecha_de_entrega;
                $start = $end->copy()->subHour(); 

                return [
                    'id'         => 'delivery_' . $t->id,
                    'resourceId' => $t->usuario_id,
                    'start'      => $start->toIso8601String(),
                    'end'        => $end->toIso8601String(),
                    'title'      => '📅 Entrega: ' . $t->titulo,
                    'backgroundColor' => '#dc3545', // Rojo para diferenciar
                    'borderColor'     => '#dc3545',
                    'extendedProps' => [
                        'type'     => 'delivery',
                        'tarea_id' => $t->id,
                    ],
                ];
            });

        return response()->json($bloques->merge($entregas));
    }
}