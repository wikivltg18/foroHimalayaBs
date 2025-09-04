<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapaArea extends Model
{
    protected $table = 'mapa_areas';
    protected $fillable = ['mapa_del_cliente_id', 'area_id', 'horas_contratadas'];

    public function mapa(): BelongsTo
    {
        return $this->belongsTo(MapaDelCliente::class, 'mapa_del_cliente_id');
    }
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}