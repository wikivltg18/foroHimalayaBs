<?php

namespace App\Models;

use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaseServicio extends Model
{

    protected $fillable = [
        'tipo_servicio_id',
        'nombre',
        'descripcion',
    ];

    public function tipoServicio():BelongsTo
{
    return $this->belongsTo(TipoServicio::class);
}

}