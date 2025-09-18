<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea_time_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('tarea_id');                 // tarea_servicios.id
            $table->unsignedBigInteger('usuario_id'); // users.id

            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->decimal('duracion_h', 8, 3); // redundancia útil para reportes
            $table->string('nota', 255)->nullable();

            $table->timestamps();

            $table->index(['tarea_id', 'started_at']);
            $table->index(['usuario_id', 'started_at']);

            $table->foreign('tarea_id')
                ->references('id')->on('tarea_servicios')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('usuario_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_time_logs');
    }
};