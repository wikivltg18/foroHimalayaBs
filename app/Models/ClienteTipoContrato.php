<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClienteTipoContrato extends Pivot
{
    // Definir la tabla asociada
    protected $table = 'cliente_tipo_contrato';

    // Definir los campos que se pueden llenar
    protected $fillable = [
        'cliente_id',
        'tipo_contrato_id',
    ];
}