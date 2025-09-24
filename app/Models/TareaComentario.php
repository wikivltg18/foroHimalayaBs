<?php

namespace App\Models;

use App\Models\User;
use App\Models\TareaServicio;
use Illuminate\Database\Eloquent\Model;

class TareaComentario extends Model
{
    protected $table = 'tarea_comentarios';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'tarea_id', 'usuario_id', 'comentario'];

    // Relaciones
    // Relación con la tarea a la que pertenece el comentario
    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }

    // Relación con el usuario que hizo el comentario
    public function autor()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}