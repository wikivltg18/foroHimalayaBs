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
        Schema::table('users', function (Blueprint $table) {
        $table->string('foto_perfil')->nullable();
        $table->string('telefono')->nullable();
        $table->date('f_nacimiento')->nullable();
        $table->integer('h_defecto')->nullable();

        // Claves foráneas si existen las tablas relacionadas
        $table->unsignedBigInteger('id_cargo')->nullable();
        $table->unsignedBigInteger('id_area')->nullable();
        $table->unsignedBigInteger('id_rol')->nullable();

        // Opcionalmente puedes agregar las foreign keys
        $table->foreign('id_cargo')->references('id')->on('cargos')->onDelete('set null');
        $table->foreign('id_area')->references('id')->on('areas')->onDelete('set null');
        $table->foreign('id_rol')->references('id')->on('roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['id_cargo']);
        $table->dropColumn('id_cargo');

        $table->dropForeign(['id_area']);
        $table->dropColumn('id_area');

        $table->dropForeign(['id_rol']);
        $table->dropColumn('id_rol');

        $table->dropColumn('telefono');
        $table->dropColumn('f_nacimiento');
        $table->dropColumn('foto_perfil');
        $table->dropColumn('h_defecto');
        });
    }
};