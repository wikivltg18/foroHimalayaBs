<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea_usuarios', function (Blueprint $table) {
            $table->uuid('tarea_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->primary(['tarea_id', 'usuario_id']);

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
        Schema::dropIfExists('tarea_usuarios');
    }
};