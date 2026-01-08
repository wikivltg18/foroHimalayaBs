<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TareaBloque extends Model
{
    use HasUuids;

    protected $table = 'tarea_bloques';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','tarea_id','user_id','scheduled_by','inicio','fin','orden'];

    protected $casts = [
        'inicio'       => 'datetime',
        'fin'          => 'datetime',
        'orden'        => 'integer',
        'scheduled_by' => 'integer',
    ];

    public function tarea()       { return $this->belongsTo(TareaServicio::class, 'tarea_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id'); }
    public function scheduledBy() { return $this->belongsTo(User::class, 'scheduled_by'); }
    public function gcal()        { return $this->hasOne(TaskCalendarBlockEvent::class, 'tarea_bloque_id'); }
}