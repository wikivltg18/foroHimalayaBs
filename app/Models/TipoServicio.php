<?php

namespace App\Models;

use App\Models\Modalidad;
use App\Models\FaseServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoServicio extends Model
{

    protected $fillable = [
        'modalidad_id',
        'nombre',
        'descripcion',
    ];

    public function modalidad()
{
    return $this->belongsTo(Modalidad::class);
}

 public function fases(): HasMany
    {
        return $this->hasMany(FaseServicio::class, 'tipo_servicio_id');
    }

}