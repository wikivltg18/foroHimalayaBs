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
        Schema::create('fases_de_servicio_instancias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('servicio_id');
            $table->unsignedBigInteger('fase_servicio_id');

            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('posicion')->default(0);

            $table->timestamps();

            $table->foreign('servicio_id')
                ->references('id')
                ->on('servicios')
                ->onDelete('cascade');

            $table->foreign('fase_servicio_id')
                ->references('id')
                ->on('fases_de_servicio')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fases_de_servicio_instancias');
    }
};