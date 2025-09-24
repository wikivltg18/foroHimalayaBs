<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoTableroServicio extends Model
{
    protected $table = "estado_tablero_servicios";
    protected $fillable = ['nombre'];
}