<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea_comentarios', function (Blueprint $table) {
            // Si tus IDs de tarea son UUID (string 36), usa uuid:
            $table->uuid('id')->primary();

            // Clave foránea a tarea (uuid). Si tu tarea usa string/uuid, deja uuid; si no, usa el tipo que corresponda.
            $table->uuid('tarea_id')->index();

            // Usuario autor (normalmente unsignedBigInteger)
            $table->unsignedBigInteger('usuario_id')->index();

            // HTML del comentario (desde Quill, ya sanitizado en el servidor)
            $table->longText('comentario');

            $table->timestamps();

            // ===== Opcional: agrega FKs si los tipos coinciden exactamente =====
            // Si tarea_servicios.id es uuid también:
            // $table->foreign('tarea_id')->references('id')->on('tarea_servicios')->onDelete('cascade');

            // Si users.id es unsignedBigInteger (lo usual):
            // $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_comentarios');
    }
};