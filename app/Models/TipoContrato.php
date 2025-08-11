<?php

namespace App\Models;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoContrato extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

public function clientes(): BelongsToMany
{
    return $this->belongsToMany(Cliente::class, 'cliente_tipo_contrato', 'tipo_contrato_id', 'cliente_id')
                ->using(ClienteTipoContrato::class);  // Usando el modelo pivot
}
}