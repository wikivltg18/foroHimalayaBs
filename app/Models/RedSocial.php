<?php

namespace App\Models;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedSocial extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'red_socials';
    // Definir los campos que se pueden asignar masivamente
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
        // Definir la relación de pertenencia a Cliente
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
}