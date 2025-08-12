<?php

namespace App\Models;

use App\Models\User;
use App\Models\RedSocial;
use App\Models\TipoContrato;
use App\Models\EstadoCliente;
use App\Models\ClienteTipoContrato;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cliente extends Model
{

    // Definir los campos que se pueden llenar
    protected $fillable = [
        'logo',
        'nombre',
        'correo_electronico',
        'telefono',
        'sitio_web',
        'id_usuario',
        'id_estado_cliente',
    ];

    /**
     * Relación con el modelo Usuario.
     */
    public function usuario(): BelongsTo
    {
        // Relación uno a muchos con el modelo User
        // Asumiendo que el campo 'id_usuario' en la tabla 'clientes'
        // es la clave foránea que referencia a la tabla 'users'
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Relación con el modelo EstadoCliente.
     */
    public function estado(): BelongsTo
    {
        // Relación uno a muchos con el modelo EstadoCliente
        // Asumiendo que el campo 'id_estado_cliente' en la tabla 'clientes'
        // es la clave foránea que referencia a la tabla 'estado_clientes'
        return $this->belongsTo(EstadoCliente::class, 'id_estado_cliente');
    }

    /**
     * Relación con el modelo TipoContrato.
     */
    public function tiposContrato(): BelongsToMany
    {
        // Relación muchos a muchos con el modelo TipoContrato
        // Asumiendo que existe una tabla pivot 'cliente_tipo_contrato'
        // con las columnas 'cliente_id' y 'tipo_contrato_id'
        // Usando el modelo ClienteTipoContrato para la tabla pivot
        return $this->belongsToMany(TipoContrato::class, 'cliente_tipo_contrato', 'cliente_id', 'tipo_contrato_id')
                    ->using(ClienteTipoContrato::class); // Usando el modelo pivot
    }

    /**
     * Relación con el modelo RedSocial.
     */
        public function redSocial(): HasMany
    {
        // Relación uno a muchos con el modelo RedSocial
        // Asumiendo que el campo 'id_cliente' en la tabla 'redes_sociales'
        // es la clave foránea que referencia a la tabla 'clientes'
        return $this->hasMany(RedSocial::class, 'id_cliente');
    }

}