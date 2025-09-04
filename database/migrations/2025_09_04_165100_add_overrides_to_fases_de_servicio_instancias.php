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
            // NUEVOS: overrides explícitos (deja tus columnas actuales si las usas)
            $table->string('nombre_custom', 200)->nullable()->after('descripcion');
            $table->text('descripcion_custom')->nullable()->after('nombre_custom');

            // OPCIONAL: snapshot + versionado de la plantilla (para estabilidad)
            $table->json('plantilla_snapshot')->nullable()->after('descripcion_custom');
            $table->unsignedInteger('plantilla_version')->nullable()->after('plantilla_snapshot');

            // Sugerencias de índices
            $table->index(['servicio_id', 'posicion']);
            $table->index(['servicio_id', 'fase_servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::table('fases_de_servicio_instancias', function (Blueprint $table) {
            $table->dropColumn(['nombre_custom', 'descripcion_custom', 'plantilla_snapshot', 'plantilla_version']);
            $table->dropIndex(['servicio_id', 'posicion']);
            $table->dropIndex(['servicio_id', 'fase_servicio_id']);
        });
    }
};