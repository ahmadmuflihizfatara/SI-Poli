<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Grup Telegram tempat bot ditambahkan; pesan kontrol harian dikirim ke yang aktif. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('judul')->nullable();
            $table->string('tipe', 20);
            $table->boolean('aktif')->default(true)->index();
            $table->bigInteger('ditambahkan_oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_chats');
    }
};
