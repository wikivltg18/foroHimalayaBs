<?php

namespace App\Models;

use App\Models\TableroServicio;
use Illuminate\Database\Eloquent\Model;

class ColumnaTableroServicio extends Model
{
    protected $table = 'columnas_tablero_servicio';

    protected $fillable = [
        'tablero_servicio_id',
        'nombre_de_la_columna',
        'descripcion',
        'orden',
    ];

    public function tablero()
    {
        return $this->belongsTo(TableroServicio::class, 'tablero_servicio_id');
    }
}