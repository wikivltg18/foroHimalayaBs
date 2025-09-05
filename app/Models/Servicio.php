<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Modalidad;
use App\Models\TipoServicio;
use App\Models\MapaDelCliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}