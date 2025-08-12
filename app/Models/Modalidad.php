<?php

namespace App\Models;

use App\Models\FaseServicio;
use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Modalidad extends Model
{
    // Definir la tabla
    protected $table = 'modalidads';

    // Definir los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Definir las relaciones
    public function tiposDeServicios(): HasMany
    {
        // Relación uno a muchos con TipoServicio
        return $this->hasMany(TipoServicio::class);
    }

    // Relación uno a muchos a través de TipoServicio con FaseServicio
    /**
     * Obtener las fases de servicio a través de los tipos de servicio.
     */
    public function fasesDeServicios(): HasManyThrough
    {
        // Relación uno a muchos a través de TipoServicio con FaseServicio
        // Modalidad -> TipoServicio -> FaseServicio
        // 'modalidad_id' es la FK en tipo_servicios que apunta a modalidades
        // 'tipo_servicio_id' es la FK en fases_servicios que apunta a tipo_servicios
        // 'id' es la local key en modalidades
        // 'id' es la local key en tipo_servicios
        return $this->hasManyThrough(
            FaseServicio::class,
            TipoServicio::class,
            'modalidad_id',
            'tipo_servicio_id',
            'id', 
            'id'
        );
    }
}