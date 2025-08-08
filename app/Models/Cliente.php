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
    protected $table = 'clientes'; // opcional, por si la tabla tiene nombre personalizado

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
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Relación con el modelo EstadoCliente.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoCliente::class, 'id_estado_cliente');
    }

    /**
     * Relación con el modelo TipoContrato.
     */
public function tiposContrato(): BelongsToMany
{
    return $this->belongsToMany(TipoContrato::class, 'cliente_tipo_contrato', 'cliente_id', 'tipo_contrato_id')
                ->using(ClienteTipoContrato::class); // Usando el modelo pivot
}



    /**
     * Relación con el modelo RedSocial.
     */
        public function redSocial(): HasMany
    {
        return $this->hasMany(RedSocial::class, 'id_cliente');
    }

}
