// database/migrations/2025_01_01_000002_create_tarea_bloques_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tarea_bloques', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tarea_id');
            $t->unsignedBigInteger('user_id');       // colaborador
            $t->unsignedBigInteger('scheduled_by')->nullable(); // gestor que agenda
            $t->dateTime('inicio');
            $t->dateTime('fin');
            $t->unsignedSmallInteger('orden')->default(1);
            $t->timestamps();

            $t->index(['user_id', 'inicio', 'fin']);
            $t->index(['tarea_id', 'user_id']);
            $t->foreign('tarea_id')->references('id')->on('tarea_servicios')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('scheduled_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tarea_bloques');
    }
};