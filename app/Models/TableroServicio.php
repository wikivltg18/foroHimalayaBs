<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Servicio;
use Illuminate\Support\Str;
use App\Models\ColumnaTableroServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableroServicio extends Model
{
    use SoftDeletes;

    protected $table = 'tableros_servicio';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nombre_del_tablero',
        'servicio_id',
        'cliente_id',
        'estado_tablero_id',
        'nombre_del_servicio',
        'nombre_cliente',
        'nombre_modalidad',
        'nombre_tipo_de_servicio',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    public function estado()
    {
        return $this->belongsTo(EstadoTableroServicio::class, 'estado_tablero_id');
    }

    public function columnas()
    {
        return $this->hasMany(ColumnaTableroServicio::class, 'tablero_servicio_id')->orderBy('orden');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}