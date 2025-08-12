<?php

namespace App\Models;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoContrato extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'tipo_contratos';

    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación muchos a muchos con el modelo Cliente.
     */
public function clientes(): BelongsToMany
{
    // Definir la relación muchos a muchos con Cliente
    // Usando una tabla pivot llamada 'cliente_tipo_contrato'
    // y el modelo pivot ClienteTipoContrato
    return $this->belongsToMany(Cliente::class, 'cliente_tipo_contrato', 'tipo_contrato_id', 'cliente_id')
                ->using(ClienteTipoContrato::class);  // Usando el modelo pivot
}
}