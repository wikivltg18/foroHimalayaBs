<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tableros_servicio', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nombre_del_tablero', 150);

            // 🚩 Si tus tablas servicios/clientes usan BIGINT (lo usual):
            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Si servicios/clientes usan UUID, cambia a:
            // $table->foreignUuid('servicio_id')->constrained('servicios')->cascadeOnUpdate()->restrictOnDelete();
            // $table->foreignUuid('cliente_id')->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('estado_tablero_id')
                ->constrained('estado_tablero_servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Campos denormalizados (opcionales)
            $table->string('nombre_del_servicio', 80)->nullable();
            $table->string('nombre_cliente', 150)->nullable();
            $table->string('nombre_modalidad', 50)->nullable();
            $table->string('nombre_tipo_de_servicio', 150)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tableros_servicio');
    }
};