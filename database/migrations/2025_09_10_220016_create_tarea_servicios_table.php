<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('columna_id'); // ⬅️ FK a columnas_tablero_servicio.uuid

            // Estas suelen ser BIGINT en la mayoría de proyectos; ajusta si tus tablas usan UUID
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();

            $table->string('titulo', 255);
            $table->longText('descripcion');
            $table->decimal('tiempo_estimado_h', 6, 2)->default(0);

            // ⬅️ coherente con tu modelo/formulario
            $table->date('fecha_de_entrega')->nullable();

            $table->unsignedInteger('posicion')->default(1);

            $table->dateTime('finalizada_at')->nullable();
            $table->unsignedBigInteger('finalizada_por')->nullable();
            $table->boolean('archivada')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['columna_id', 'posicion'], 'idx_tarea_columna_posicion');
            $table->index(['archivada', 'finalizada_at'], 'idx_tarea_archivada_finalizada');
            $table->index('estado_id');
            $table->index('area_id');
            $table->index('usuario_id');
            $table->index('fecha_de_entrega');

            // FKs
            $table->foreign('columna_id', 'fk_tarea_columna')
                ->references('id')->on('columnas_tablero_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('estado_id', 'fk_tarea_estado')
                ->references('id')->on('estado_tarea')      // tu tabla confirmada
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('area_id', 'fk_tarea_area')
                ->references('id')->on('areas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('usuario_id', 'fk_tarea_usuario')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('finalizada_por', 'fk_tarea_finalizada_por')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_servicios');
    }
};