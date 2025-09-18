<?php

namespace App\Models;

use App\Models\User;
use App\Models\EstadoTarea;
use App\Models\TareaServicio;
use Illuminate\Database\Eloquent\Model;

class TareaEstadoHistorial extends Model
{
    protected $table = 'tarea_estados_historial';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tarea_id',
        'cambiado_por',
        'estado_id_anterior',
        'estado_id_nuevo',
    ];

    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }
    public function autor()
    {
        return $this->belongsTo(User::class, 'cambiado_por');
    }
    public function estadoDesde()
    {
        return $this->belongsTo(EstadoTarea::class, 'estado_id_anterior');
    }
    public function estadoHasta()
    {
        return $this->belongsTo(EstadoTarea::class, 'estado_id_nuevo');
    }
}