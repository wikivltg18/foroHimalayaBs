<?php

namespace App\Models;

use App\Models\TareaServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TareaRecurso extends Model
{
    use SoftDeletes;
    protected $table = "tarea_recursos";
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tarea_id',
        'tipo',
        'titulo',
        'url',
        'path',
        'mime',
        'size_bytes',
        'hash_sha256',
        'orden'
    ];

    // Relaciones
    // Relación con la tarea a la que pertenece el recurso
    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }
}