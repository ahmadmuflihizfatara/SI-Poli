<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            // Log tetap ada walau user/keluhan dihapus; pesan sudah menyimpan konteksnya.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('aksi', ['Tambah', 'Edit']);
            $table->string('sumber_daya', 50);
            $table->foreignId('keluhan_id')->nullable()->constrained('keluhan')->nullOnDelete();
            $table->text('pesan');
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
