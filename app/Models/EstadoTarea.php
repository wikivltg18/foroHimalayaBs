<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class EstadoTarea extends Model
{
    protected $table = 'estado_tarea';

    // Ajusta estos nombres a los que realmente usas en tu tabla
    protected $fillable = ['id', 'nombre', 'slug', 'color'];

    /**
     * IDs de estados "en trabajo". Ajusta los nombres a tu realidad.
     * ¡OJO! No incluyas "Finalizada" aquí.
     */
    public static function wipIds(): array
    {
        return cache()->remember('estado_wip_ids', now()->addMinutes(30), function () {
            return static::query()
                ->whereIn('nombre', ['Programada', 'En Progreso', 'En revisión']) // <-- ajusta
                ->pluck('id')
                ->all();
        });
    }

    /**
     * IDs de estados finales (cerrada/completada).
     */
    public static function finalIds(): array
    {
        return cache()->remember('estado_final_ids', now()->addMinutes(30), function () {
            return static::query()
                ->whereIn('nombre', ['Finalizada', 'Completada']) // <-- ajusta
                ->pluck('id')
                ->all();
        });
    }

    /**
     * Helper para invalidar los caches cuando edites/crees estados.
     */
    public static function refreshCache(): void
    {
        cache()->forget('estado_wip_ids');
        cache()->forget('estado_final_ids');
    }
}