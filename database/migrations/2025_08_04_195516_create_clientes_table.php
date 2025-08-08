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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable(); // Ruta o nombre del archivo
            $table->string('nombre');
            $table->string('correo_electronico')->unique();
            $table->string('telefono')->nullable();
            $table->string('sitio_web')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_estado_cliente');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_estado_cliente')->references('id')->on('estado_clientes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
