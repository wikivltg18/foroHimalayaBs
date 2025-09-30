<?php

namespace App\Observers;

use App\Models\TareaServicio;

class TareaServicioObserver
{
    public function creating(TareaServicio $tarea)
    {
        $tablero = optional($tarea->columna)->tablero;
        if ($tablero && $tablero->isTerminated()) {
            throw new \DomainException('No puedes crear tareas en un tablero terminado.');
        }
    }

    public function updating(TareaServicio $tarea)
    {
        $tablero = optional($tarea->columna)->tablero;
        if ($tablero && $tablero->isTerminated()) {
            throw new \DomainException('El tablero está terminado: no se puede modificar la tarea.');
        }
    }
}