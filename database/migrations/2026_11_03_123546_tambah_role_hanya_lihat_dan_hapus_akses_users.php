<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Role hanya-lihat (asisten kesehatan, pengasuh) menggantikan akses Tambah/Edit per akun. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat', 'psikolog', 'dokter', 'asisten_kesehatan', 'pengasuh'])->default('perawat')->change();
            $table->dropColumn(['akses_tambah', 'akses_edit']);
        });
    }

    public function down(): void
    {
        DB::table('users')->whereIn('role', ['asisten_kesehatan', 'pengasuh'])->update(['role' => 'perawat']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat', 'psikolog', 'dokter'])->default('perawat')->change();
            $table->boolean('akses_tambah')->default(true)->after('role');
            $table->boolean('akses_edit')->default(true)->after('akses_tambah');
        });
    }
};
