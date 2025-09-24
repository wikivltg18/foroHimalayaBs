<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('columnas_tablero_servicio', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nombre_de_la_columna', 255);
            $table->text('descripcion')->nullable();

            $table->uuid('tablero_servicio_id');                  // ⬅️ UUID, no BIGINT
            $table->unsignedInteger('posicion')->default(1);

            $table->timestamps();

            $table->foreign('tablero_servicio_id', 'fk_columna_tablero')
                ->references('id')->on('tableros_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['tablero_servicio_id', 'posicion'], 'idx_columna_tablero_posicion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('columnas_tablero_servicio');
    }
};