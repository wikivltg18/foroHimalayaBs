<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea_recursos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('tarea_id');                 // tarea_servicios.id
            $table->enum('tipo', ['image', 'file', 'link']);

            $table->string('titulo', 150)->nullable();
            $table->string('url', 2048)->nullable();   // para links o archivos públicos
            $table->string('path', 255)->nullable();   // ruta en storage
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('hash_sha256', 64)->nullable();

            $table->unsignedInteger('orden')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tarea_id', 'tipo', 'orden'], 'idx_recurso_tarea_tipo_orden');

            $table->foreign('tarea_id')
                ->references('id')->on('tarea_servicios')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_recursos');
    }
};