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
        Schema::create('tableros_servicio', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nombre_del_tablero', 150);

            $table->foreignUuid('servicio_id');
            $table->foreignUuid('cliente_id');
            $table->foreignId('estado_tablero_id')->constrained('estado_tablero_servicios');

            $table->string('nombre_del_servicio', 80)->nullable();
            $table->string('nombre_cliente', 150)->nullable();
            $table->string('nombre_modalidad', 50)->nullable();
            $table->string('nombre_tipo_de_servicio', 150)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tableros_servicio');
    }
};
