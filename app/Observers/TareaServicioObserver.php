<?php

namespace App\Observers;

use App\Models\TareaServicio;
use App\Jobs\CreateTaskCalendarEvent;
use App\Jobs\RemoveTaskCalendarEvent;
use App\Jobs\RemoveTaskBlocksFromCalendarJob;

class TareaServicioObserver
{
    /**
     * Handle the TareaServicio "created" event.
     */
    public function created(TareaServicio $tarea): void
    {
        // Si la tarea nace con usuario y fecha de entrega, crear evento
        if ($tarea->usuario_id && $tarea->fecha_de_entrega) {
            dispatch(new CreateTaskCalendarEvent($tarea->id, $tarea->usuario_id))->onQueue('calendar');
        }
    }

    /**
     * Handle the TareaServicio "updated" event.
     */
    public function updated(TareaServicio $tarea): void
    {
        // Detectar cambios relevantes
        $userIdOriginal = $tarea->getOriginal('usuario_id');
        $userIdNuevo    = $tarea->usuario_id;
        
        $fechaOriginal  = $tarea->getOriginal('fecha_de_entrega'); // Carbon or string
        $fechaNueva     = $tarea->fecha_de_entrega;

        // Normalizar fechas para comparación (pueden ser nulas)
        // Ojo: getOriginal devuelve string o null si no está casteado, pero Model cast 'datetime' lo convierte a Carbon si se accede via atributo, pero getOriginal raw... depende. En Laravel moderno getOriginal respeta casts si se pide.
        // Mejor usaremos isDirty o comparacion directa de valores.
        
        $userChanged  = $tarea->isDirty('usuario_id');
        $dateChanged  = $tarea->isDirty('fecha_de_entrega');

        if (!$userChanged && !$dateChanged) {
            return;
        }

        // CASO 1: Se eliminó la fecha de entrega -> Borrar evento
        if ($dateChanged && $fechaOriginal && !$fechaNueva) {
            dispatch(new RemoveTaskCalendarEvent($tarea->id))->onQueue('calendar');
            return;
        }

        // CASO 2: Se cambió el usuario (y hay fecha) -> Borrar anterior, Crear nuevo
        if ($userChanged && $fechaNueva) {
            // Borrar del anterior dueño si existía
            if ($userIdOriginal) {
                dispatch(new RemoveTaskCalendarEvent($tarea->id, $userIdOriginal))->onQueue('calendar');
            }
            // Crear para el nuevo
            if ($userIdNuevo) {
                dispatch(new CreateTaskCalendarEvent($tarea->id, $userIdNuevo))->onQueue('calendar');
            }
            return;
        }

        // CASO 3: Solo cambió la fecha (mismo usuario) -> Actualizar (que es borrar + crear en este esquema simple)
        if ($dateChanged && $fechaNueva && $userIdNuevo) {
             // Si había fecha antes, borramos el previo
             if ($fechaOriginal) {
                 dispatch(new RemoveTaskCalendarEvent($tarea->id))->onQueue('calendar');
             }
             // Creamos el nuevo
             dispatch(new CreateTaskCalendarEvent($tarea->id, $userIdNuevo))->onQueue('calendar');
        }
    }

    /**
     * Handle the TareaServicio "deleted" event.
     */
    public function deleted(TareaServicio $tarea): void
    {
        // Borrar evento de fecha de entrega
        dispatch(new RemoveTaskCalendarEvent($tarea->id))->onQueue('calendar');
        
        // Borrar bloques de agenda si existieran (aunque la lógica de borrado de tarea debería encargarse, el observer es un buen backup o el lugar principal)
        dispatch(new RemoveTaskBlocksFromCalendarJob($tarea->id))->onQueue('calendar');
    }

    /**
     * Handle the TareaServicio "restored" event.
     */
    public function restored(TareaServicio $tarea): void
    {
        // Si se restaura y tiene fecha futura, ¿recrear?
        if ($tarea->usuario_id && $tarea->fecha_de_entrega && $tarea->fecha_de_entrega->isFuture()) {
            dispatch(new CreateTaskCalendarEvent($tarea->id, $tarea->usuario_id))->onQueue('calendar');
        }
    }

    /**
     * Handle the TareaServicio "force deleted" event.
     */
    public function forceDeleted(TareaServicio $tarea): void
    {
         dispatch(new RemoveTaskCalendarEvent($tarea->id))->onQueue('calendar');
         dispatch(new RemoveTaskBlocksFromCalendarJob($tarea->id))->onQueue('calendar');
    }
}