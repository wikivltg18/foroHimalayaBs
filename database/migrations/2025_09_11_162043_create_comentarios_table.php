<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('commentable'); // commentable_type, commentable_id
            $table->unsignedBigInteger('usuario_id');
            $table->longText('contenido_html'); // lo que guardes desde Quill (sanitizado)
            $table->json('meta')->nullable();   // menciones, adjuntos livianos, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id', 'created_at'], 'idx_commentable_ts');
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};