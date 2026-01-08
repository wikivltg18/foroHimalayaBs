<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_calendar_block_events', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tarea_bloque_id')->unique();
            $t->unsignedBigInteger('user_id');
            $t->string('calendar_id');
            $t->string('google_event_id');
            $t->timestamps();

            $t->foreign('tarea_bloque_id')->references('id')->on('tarea_bloques')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->index(['user_id', 'google_event_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('task_calendar_block_events');
    }
};