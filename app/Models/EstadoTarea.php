<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoTarea extends Model
{
    protected $table = "estado_tarea";
    protected $fillable = ['nombre'];


    public static function wipIds(): array
    {
        return cache()->remember('wip_ids', now()->addMinutes(30), function () {
            return static::query()
                ->whereIn('nombre', ['Programada', 'En Progreso', 'En revisión', 'Finalizada'])
                ->pluck('id')
                ->all();
        });
    }
}