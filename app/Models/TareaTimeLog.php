<?php

namespace App\Models;

use App\Models\User;
use App\Models\TareaServicio;
use Illuminate\Database\Eloquent\Model;

class TareaTimeLog extends Model
{
    protected $table = 'tarea_time_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tarea_id',
        'usuario_id',
        'started_at',
        'ended_at',
        'duracion_h',
        'nota'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'duracion_h' => 'decimal:3',
    ];

    // Relaciones
    // Relación con la tarea a la que pertenece el time log
    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }

    // Relación con el usuario que registró el time log
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}