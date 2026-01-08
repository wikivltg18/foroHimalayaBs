<?php

namespace App\Models;

use App\Models\TareaBloque;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TaskCalendarBlockEvent extends Model
{
    use HasUuids;

    protected $table = 'task_calendar_block_events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','tarea_bloque_id','user_id','calendar_id','google_event_id'];

    public function bloque() { return $this->belongsTo(TareaBloque::class, 'tarea_bloque_id'); }
    public function user()   { return $this->belongsTo(User::class, 'user_id'); }
}