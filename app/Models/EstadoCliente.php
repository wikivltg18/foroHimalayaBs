<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCliente extends Model
{
    // Definir la tabla asociada
    protected $table = 'estado_clientes';
    protected $fillable = ['nombre', 'descripcion'];
}