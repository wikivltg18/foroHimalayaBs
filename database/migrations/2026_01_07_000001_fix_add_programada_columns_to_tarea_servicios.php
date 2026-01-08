<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tarea_servicios', function (Blueprint $t) {
            if (! Schema::hasColumn('tarea_servicios', 'programada_inicio')) {
                $t->dateTime('programada_inicio')->nullable()->after('fecha_de_entrega');
            }
            if (! Schema::hasColumn('tarea_servicios', 'programada_fin')) {
                $t->dateTime('programada_fin')->nullable()->after('programada_inicio');
            }
            // index creation intentionally omitted to avoid duplicate-index issues during tests (original migration already creates it)
        });
    }

    public function down(): void {
        Schema::table('tarea_servicios', function (Blueprint $t) {
            if (Schema::hasColumn('tarea_servicios', 'programada_inicio')) {
                $t->dropColumn('programada_inicio');
            }
            if (Schema::hasColumn('tarea_servicios', 'programada_fin')) {
                $t->dropColumn('programada_fin');
            }
        });
    }
};