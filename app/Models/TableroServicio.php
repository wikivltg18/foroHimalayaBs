<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Servicio;
use Illuminate\Support\Str;
use App\Models\EstadoTableroServicio;
use App\Models\ColumnaTableroServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableroServicio extends Model
{
    use SoftDeletes;

    protected $table = 'tableros_servicio';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nombre_del_tablero',
        'servicio_id',
        'cliente_id',
        'estado_tablero_id',
        'nombre_del_servicio',
        'nombre_cliente',
        'nombre_modalidad',
        'nombre_tipo_de_servicio',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    public function estado()
    {
        return $this->belongsTo(EstadoTableroServicio::class, 'estado_tablero_id');
    }

    public function columnas()
    {
        return $this->hasMany(ColumnaTableroServicio::class, 'tablero_servicio_id')->orderBy('posicion');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Todas las tareas del tablero (vía columnas).
     */
    public function tareas()
    {
        return $this->hasManyThrough(
            TareaServicio::class,
            ColumnaTableroServicio::class,
            'tablero_servicio_id', // FK en columnas
            'columna_id',          // FK en tareas
            'id',                  // local key en tablero
            'id'                   // local key en columna
        );
    }

    /** Helpers de estado */
    public function isTerminated(): bool
{
    return optional($this->estado)->nombre === 'Terminado';
}

public function canBeFinalized(): bool
{
    $finalIds = \App\Models\EstadoTarea::finalIds();
    return !$this->tareas()
        ->where(function ($q) use ($finalIds) {
            $q->whereNull('estado_id')->orWhereNotIn('estado_id', $finalIds);
        })
        ->exists();
}

public function markAsTerminated(): void
{
    if (!$this->canBeFinalized()) {
        throw new \DomainException('No puedes finalizar el tablero: hay tareas pendientes.');
    }
    $estado = \App\Models\EstadoTableroServicio::where('nombre', 'Terminado')->firstOrFail();
    $this->estado()->associate($estado);
    $this->save();
}

public function markAsActive(): void
{
    $estado = \App\Models\EstadoTableroServicio::where('nombre', 'Activo')->firstOrFail();
    $this->estado()->associate($estado);
    $this->save();
}

    /** (Opcional) contador de pendientes sin scopes, por si lo necesitas en Blade */
    public function pendingTasksCount(): int
    {
        $final = \App\Models\EstadoTarea::finalIds();
        return $this->tareas()
            ->where(function ($q) use ($final) {
                $q->whereNull('estado_id')->orWhereNotIn('estado_id', $final);
            })
            ->count();
    }

}