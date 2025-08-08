<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClienteTipoContrato extends Pivot
{
    protected $table = 'cliente_tipo_contrato';

    protected $fillable = [
        'cliente_id',
        'tipo_contrato_id',
    ];
}
