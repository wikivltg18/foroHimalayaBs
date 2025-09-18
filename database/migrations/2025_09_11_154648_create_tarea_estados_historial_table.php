<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarea_estados_historial', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('tarea_id');
            $table->unsignedBigInteger('cambiado_por');

            $table->unsignedBigInteger('estado_id_anterior')->nullable();
            $table->unsignedBigInteger('estado_id_nuevo');

            $table->timestamps();

            $table->index(['tarea_id', 'created_at'], 'idx_hist_tarea_fecha');

            $table->foreign('tarea_id')
                ->references('id')->on('tarea_servicios')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('cambiado_por')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('estado_id_anterior')
                ->references('id')->on('estado_tarea')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('estado_id_nuevo')
                ->references('id')->on('estado_tarea')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_estados_historial');
    }
};