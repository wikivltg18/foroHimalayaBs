<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCalendarEvent extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tarea_id', 'user_id', 'calendar_id', 'google_event_id'];

    public function tarea()
    {
        return $this->belongsTo(TareaServicio::class, 'tarea_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}