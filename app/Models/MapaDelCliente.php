<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Clase que representa el modelo MapaDelCliente, vinculado a la tabla 'mapa_del_cliente'
class MapaDelCliente extends Model
{
    // Nombre explícito de la tabla en la base de datos
    protected $table = 'mapa_del_cliente';

    // Campos que se pueden asignar masivamente
    protected $fillable = ['servicio_id'];

    /**
     * Relación inversa: cada mapa del cliente pertenece a un servicio.
     * Define una relación BelongsTo con el modelo Servicio.
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación uno a muchos: un mapa del cliente puede tener muchas áreas.
     * Define una relación HasMany con el modelo MapaArea.
     */
    public function mapaAreas(): HasMany
    {
        return $this->hasMany(MapaArea::class, 'mapa_del_cliente_id');
    }
}