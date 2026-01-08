<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskCalendarEvent extends Model
{
    use HasUuids;        // Genera UUIDs para 'id'
    // use SoftDeletes;  // Descomenta si quieres borrado lógico

    protected $table = 'task_calendar_events'; // explícito (opcional)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tarea_id',
        'user_id',
        'calendar_id',
        'google_event_id',
    ];

    protected $casts = [
        'id'           => 'string',
        'tarea_id'     => 'string',  // asumiendo que es UUID; si no, cámbialo
        'user_id'      => 'integer',
        'calendar_id'  => 'string',
        'google_event_id' => 'string',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        // 'deleted_at' => 'datetime', // si usas SoftDeletes
    ];

    /** Relaciones */
    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Scopes útiles */
    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeForTarea($q, string $tareaId)
    {
        return $q->where('tarea_id', $tareaId);
    }

    /** Helper: upsert por (tarea_id, user_id) */
    public static function upsertByTaskUser(array $attributes): self
    {
        // requiere ['tarea_id','user_id','calendar_id','google_event_id']
        return static::updateOrCreate(
            [
                'tarea_id' => $attributes['tarea_id'],
                'user_id'  => $attributes['user_id'],
            ],
            [
                'calendar_id'     => $attributes['calendar_id'],
                'google_event_id' => $attributes['google_event_id'],
            ]
        );
    }
}