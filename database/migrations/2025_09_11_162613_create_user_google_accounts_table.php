<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_google_accounts', function (Blueprint $table) {
            $table->id(); // BIGINT
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('google_user_id')->unique();
            $table->string('email');
            $table->text('access_token');   // JSON del token completo
            $table->text('refresh_token')->nullable();
            $table->string('calendar_id')->nullable(); // default calendar elegido por el usuario
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index('email');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('user_google_accounts');
    }
};