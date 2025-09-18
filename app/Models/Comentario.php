<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = "comentarios";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'usuario_id', 'contenido_html', 'meta'];
    protected $casts = [
        'meta' => 'array',
    ];

    // Relaciones
    // Relación polimórfica con el modelo que recibe comentarios
    public function commentable()
    {
        return $this->morphTo();
    }

    // Relación con el usuario que hizo el comentario
    public function autor()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}