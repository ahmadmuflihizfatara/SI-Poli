<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->foreignId('pemeriksaan_id')->nullable()->after('keluhan_id')->constrained('pemeriksaan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pemeriksaan_id');
        });
    }
};
