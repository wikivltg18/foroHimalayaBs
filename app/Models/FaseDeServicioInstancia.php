<?php

namespace App\Models;

use App\Models\Servicio;
use App\Models\FaseServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaseDeServicioInstancia extends Model
{

    protected $table = 'fases_de_servicio_instancias';

    // Incluye los nuevos campos
    protected $fillable = [
        'servicio_id',
        'fase_servicio_id',
        'nombre',              // legacy (si lo sigues usando)
        'descripcion',         // legacy (si lo sigues usando)
        'posicion',
    ];

    /*
     |------------------------------
     | Relaciones
     |------------------------------
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(FaseServicio::class, 'fase_servicio_id');
    }

    /*
     |------------------------------
     | Scopes útiles
     |------------------------------
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('posicion');
    }

    /*
     |------------------------------
     | Atributos "efectivos"
     |   - Si hay override, úsalo.
     |   - Si no, cae al valor de la plantilla.
     |   - Si la plantilla no existe, usa snapshot (si lo guardaste).
     |------------------------------
     */
    public function getNombreEfectivoAttribute(): ?string
    {
        if (!empty($this->nombre_custom)) {
            return $this->nombre_custom;
        }
        if ($this->relationLoaded('plantilla') && $this->plantilla) {
            return $this->plantilla->nombre;
        }
        return $this->plantilla_snapshot['nombre'] ?? ($this->nombre ?? null); // último fallback
    }

    public function getDescripcionEfectivaAttribute(): ?string
    {
        if (!empty($this->descripcion_custom)) {
            return $this->descripcion_custom;
        }
        if ($this->relationLoaded('plantilla') && $this->plantilla) {
            return $this->plantilla->descripcion;
        }
        return $this->plantilla_snapshot['descripcion'] ?? ($this->descripcion ?? null); // último fallback
    }
}