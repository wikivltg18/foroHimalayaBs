<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_calendar_events', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tarea_id');
            $t->unsignedBigInteger('user_id');      // asignado
            $t->string('calendar_id');              // calendar donde se creó
            $t->string('google_event_id');          // id del evento en GCal
            $t->timestamps();

            $t->unique(['tarea_id', 'user_id']);     // 1 evento por usuario asignado (ajústalo si quieres varios)
            $t->foreign('tarea_id')->references('id')->on('tarea_servicios')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('task_calendar_events');
    }
};