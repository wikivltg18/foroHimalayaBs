<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\TableroServicio;
use Illuminate\Database\Eloquent\Model;

class ColumnaTableroServicio extends Model
{
    protected $table = 'columnas_tablero_servicio';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tablero_servicio_id',
        'nombre_de_la_columna',
        'descripcion',
        'posicion',
    ];


    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    public function tablero()
    {
        return $this->belongsTo(TableroServicio::class, 'tablero_servicio_id');
    }

    public function tareas()
    {
        return $this->hasMany(TareaServicio::class, 'columna_id')->orderBy('posicion');
    }
}
