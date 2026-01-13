<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarea_servicios', function (Blueprint $table) {
            // Almacenar el ID del calendario de Google seleccionado para esta tarea
            // Ejemplo: 'primary', 'calendario@gmail.com', etc.
            $table->string('google_calendar_id', 255)->nullable()->after('fecha_de_entrega');
            
            $table->index('google_calendar_id');
        });
    }

    public function down(): void
    {
        Schema::table('tarea_servicios', function (Blueprint $table) {
            $table->dropIndex(['google_calendar_id']);
            $table->dropColumn('google_calendar_id');
        });
    }
};
