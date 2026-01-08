<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tarea_servicios', function (Blueprint $t) {
            $t->dateTime('programada_inicio')->nullable()->after('fecha_de_entrega');
            $t->dateTime('programada_fin')->nullable()->after('programada_inicio');
            $t->index(['programada_inicio', 'programada_fin']);
        });
    }
    public function down(): void {
        Schema::table('tarea_servicios', function (Blueprint $t) {
            $t->dropColumn(['programada_inicio', 'programada_fin']);
        });
    }
};