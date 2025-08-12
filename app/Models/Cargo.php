<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    // Definir la tabla asociada
    protected $table = 'cargos';

    // Definir los campos que se pueden llenar
    protected $fillable = ['nombre', 'descripcion'];

    // Definir la relación con el modelo User
    public function usuarios(): HasMany
        {
            // Relación uno a muchos con el modelo User
            // Asumiendo que el campo 'id_cargo' en la tabla 'users'
            // es la clave foránea que referencia a la tabla 'cargos'
            return $this->hasMany(User::class, 'id_cargo');
        }

}