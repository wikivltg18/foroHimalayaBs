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
        Schema::create('mapa_del_cliente', function (Blueprint $t) {
            $t->id();
            $t->foreignId('servicio_id')->unique()->constrained('servicios')->cascadeOnDelete();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapa_del_clientes');
    }
};