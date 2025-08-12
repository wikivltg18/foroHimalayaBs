<?php

namespace App\Models;

use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaseServicio extends Model
{

    // Definir la tabla asociada
    protected $table = 'fase_servicios';
    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'tipo_servicio_id',
        'nombre',
        'descripcion',
    ];

    // Relación con TipoServicio
    // Esta función define la relación entre FaseServicio y TipoServicio
    // FaseServicio pertenece a un TipoServicio
    // Se utiliza para acceder al tipo de servicio asociado a una fase de servicio
    // Permite obtener el tipo de servicio al que pertenece esta fase
    public function tipoServicio():BelongsTo
    {
        // Definir la relación con TipoServicio
        return $this->belongsTo(TipoServicio::class);
    }

}