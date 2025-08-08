<?php

namespace App\Models;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedSocial extends Model
{
protected $fillable = [
    'nombre_rsocial',
    'url_rsocial',
    'id_cliente',
];

    /**
     * Relación con el modelo Cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
}
