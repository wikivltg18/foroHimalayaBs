<?php

namespace App\Models;

use App\Models\FaseServicio;
use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Modalidad extends Model
{
    protected $table = 'modalidads';
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

            public function tiposDeServicios(): HasMany
    {
        return $this->hasMany(TipoServicio::class);
    }

public function fasesDeServicios(): HasManyThrough
    {
        return $this->hasManyThrough(
            FaseServicio::class,   // final
            TipoServicio::class,   // intermedio
            'modalidad_id',        // FK en tipo_servicios que apunta a modalidades
            'tipo_servicio_id',    // FK en fases_servicios que apunta a tipo_servicios
            'id',                  // local key en modalidades
            'id'                   // local key en tipo_servicios
        );
    }
}