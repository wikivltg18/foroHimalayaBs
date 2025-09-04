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
        Schema::table('fases_de_servicio_instancias', function (Blueprint $table) {
            $table->softDeletes(); // crea columna 'deleted_at' nullable
        });
    }

    public function down(): void
    {
        Schema::table('fases_de_servicio_instancias', function (Blueprint $table) {
            $table->dropSoftDeletes(); // elimina 'deleted_at'
        });
    }
};