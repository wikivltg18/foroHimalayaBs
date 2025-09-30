<?php

namespace App\Models;

use App\Models\Area;
use App\Models\EstadoTarea;
use App\Models\TareaRecurso;
use App\Models\TareaTimeLog;
use App\Models\TareaComentario;
use App\Models\TareaEstadoHistorial;
use App\Models\ColumnaTableroServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TareaServicio extends Model
{
    use SoftDeletes;

    protected $table = "tarea_servicios";

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'columna_id',
        'estado_id',
        'area_id',
        'usuario_id',
        'titulo',
        'descripcion',
        'tiempo_estimado_h',
        'fecha_de_entrega',
        'posicion',
        'finalizada_at',
        'finalizada_por',
        'archivada',
    ];

    protected $casts = [
        'archivada'         => 'boolean',
        'finalizada_at'     => 'datetime',
        'fecha_de_entrega' => 'datetime',
        'tiempo_estimado_h' => 'decimal:2',
    ];

    // Relaciones
    // Relaciones
    public function columna()     { return $this->belongsTo(ColumnaTableroServicio::class, 'columna_id'); }
    public function estado()      { return $this->belongsTo(EstadoTarea::class, 'estado_id'); }
    public function area()        { return $this->belongsTo(Area::class, 'area_id'); }
    public function usuario()     { return $this->belongsTo(User::class, 'usuario_id'); }
    public function finalizador() { return $this->belongsTo(User::class, 'finalizada_por'); }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'tarea_usuarios', 'tarea_id', 'usuario_id');
    }

    public function recursos()
    {
        return $this->hasMany(TareaRecurso::class, 'tarea_id')->orderBy('tipo')->orderBy('orden');
    }

    public function comentarios()
    {
        return $this->hasMany(TareaComentario::class, 'tarea_id')->latest('created_at');
    }

    public function historiales()
    {
        return $this->hasMany(TareaEstadoHistorial::class, 'tarea_id')->oldest('created_at');
    }

    public function timeLogs()
    {
        return $this->hasMany(TareaTimeLog::class, 'tarea_id')->latest('started_at');
    }

    public function historialesCompletos()
{
    return $this->hasMany(TareaEstadoHistorial::class, 'tarea_id');
}

public function creadorUser() // retorna User|null
{
    $first = $this->historialesCompletos()->oldest('created_at')->first();
    return $first?->autor; // asumiendo relación 'autor' en TareaEstadoHistorial->belongsTo(User::class,'cambiado_por')
}
}