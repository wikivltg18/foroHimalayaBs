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
        Schema::create('columnas_tablero_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tablero_servicio_id')
                ->constrained('tableros_servicio')
                ->cascadeOnDelete();
            $table->string('nombre_de_la_columna', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columnas_tablero_servicio');
    }
};