<?php

namespace App\Models;

use App\Models\Modalidad;
use App\Models\FaseServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoServicio extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'tipo_servicios';

    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'modalidad_id',
        'nombre',
        'descripcion',
    ];

    /**
     * Relación de pertenencia a Modalidad.
     */
    /**
     * Obtener la modalidad a la que pertenece este tipo de servicio.
     */
    public function modalidad()
    {
        // Definir la relación de pertenencia a Modalidad
        // 'modalidad_id' es la FK en tipo_servicios que apunta a modalidades
        // 'id' es la local key en modalidades
        // Retorna una instancia de la relación BelongsTo
        // Usando el modelo Modalidad
        // Esto permite acceder a la modalidad asociada a este tipo de servicio
        return $this->belongsTo(Modalidad::class);
    }

    /**
     * Relación uno a muchos con FaseServicio.
     */
    /**
     * Obtener las fases de servicio asociadas a este tipo de servicio.
     */
    public function fases(): HasMany
    {   
        // Relación uno a muchos con FaseServicio
        // 'tipo_servicio_id' es la FK en fases_servicios que apunta a tipo_servicios
        // 'id' es la local key en tipo_servicios
        return $this->hasMany(FaseServicio::class, 'tipo_servicio_id');
    }

}