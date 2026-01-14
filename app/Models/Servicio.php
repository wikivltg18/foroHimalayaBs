<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Modalidad;
use App\Models\TipoServicio;
use App\Models\MapaDelCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servicio extends Model
{

    use SoftDeletes;
    protected $table = 'servicios';
    protected $fillable = [
        'cliente_id',
        'nombre_servicio',
        'modalidad_id',
        'tipo_servicio_id'
    ];

    public function tableros(): HasMany
    {
        return $this->hasMany(TableroServicio::class, 'servicio_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoServicio::class, 'tipo_servicio_id');
    }

    public function mapa(): HasOne
    {
        return $this->hasOne(MapaDelCliente::class, 'servicio_id');
    }
    public function fases(): HasMany
    {
        return $this->hasMany(FaseDeServicioInstancia::class, 'servicio_id')->orderBy('posicion');
    }
    public function fasesInstanciadas(): HasMany
    {
        return $this->hasMany(FaseDeServicioInstancia::class, 'servicio_id');
    }

    /**
     * IDs de las áreas contratadas (horas_contratadas > 0) para este servicio,
     * recorriendo: servicios -> mapa_del_cliente (hasOne) -> mapa_areas (many) -> areas
     */
    public function areaIdsContratadas()
    {
        // si no hay mapa para este servicio, devolvemos colección vacía
        $mapaId = optional($this->mapa)->id;
        if (!$mapaId) return collect([]);

        return DB::table('areas')
            ->join('mapa_areas', 'mapa_areas.area_id', '=', 'areas.id')
            ->where('mapa_areas.mapa_del_cliente_id', $mapaId)
            ->where('mapa_areas.horas_contratadas', '>', 0)
            ->pluck('areas.id');
    }

    /**
     * Áreas contratadas (modelos Area) para este servicio.
     */
    public function areasContratadas()
    {
        $mapaId = optional($this->mapa)->id;
        if (!$mapaId) return Area::whereRaw('1=0'); // query vacío

        return Area::query()
            ->select('areas.*')
            ->join('mapa_areas', 'mapa_areas.area_id', '=', 'areas.id')
            ->where('mapa_areas.mapa_del_cliente_id', $mapaId)
            ->where('mapa_areas.horas_contratadas', '>', 0)
            ->distinct();
    }

    /**
     * Horas contratadas para un área específica en este servicio.
     * Devuelve decimal (float). Si no hay mapa o no existe el vínculo, devuelve 0.
     */
    public function horasContratadasParaArea($areaId): float
    {
        $mapaId = optional($this->mapa)->id;
        if (!$mapaId) return 0.0;

        return (float) DB::table('mapa_areas')
            ->where('mapa_del_cliente_id', $mapaId)
            ->where('area_id', $areaId)
            ->sum('horas_contratadas'); // sum por si existiera más de un registro
    }

    /**
     * (Opcional) Mapa [area_id => horas] para todas las áreas contratadas del servicio.
     */
    public function horasContratadasPorAreaMap(): array
    {
        $mapaId = optional($this->mapa)->id;
        if (!$mapaId) return [];

        return DB::table('mapa_areas')
            ->select('area_id', DB::raw('SUM(horas_contratadas) as horas'))
            ->where('mapa_del_cliente_id', $mapaId)
            ->groupBy('area_id')
            ->pluck('horas', 'area_id')
            ->toArray();
    }
}
