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
        Schema::create('fase_servicios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_servicio_id');
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicios')->onDelete('cascade');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**  
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fase_servicios');
    }
};