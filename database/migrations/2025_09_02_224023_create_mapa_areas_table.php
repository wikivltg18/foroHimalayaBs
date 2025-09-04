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
        Schema::create('mapa_areas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('mapa_del_cliente_id')->constrained('mapa_del_cliente')->cascadeOnDelete();
            $t->foreignId('area_id')->constrained('areas');
            $t->decimal('horas_contratadas', 8, 2)->default(0);
            $t->timestamps();

            $t->unique(['mapa_del_cliente_id', 'area_id']); // una fila por área en ese mapa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapa_areas');
    }
};