<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat', 'psikolog', 'dokter'])->default('perawat')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'dokter')->update(['role' => 'perawat']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat', 'psikolog'])->default('perawat')->change();
        });
    }
};
