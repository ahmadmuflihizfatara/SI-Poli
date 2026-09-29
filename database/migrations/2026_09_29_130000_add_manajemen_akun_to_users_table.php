<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Manajemen akun: nama pengguna untuk login, akses Tambah/Edit, aktif terakhir, dan permintaan ubah kata sandi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('name');
            $table->boolean('akses_tambah')->default(true)->after('role');
            $table->boolean('akses_edit')->default(true)->after('akses_tambah');
            $table->timestamp('terakhir_aktif_at')->nullable();
            // Kata sandi baru (sudah di-hash) dari halaman lupa kata sandi, berlaku setelah disetujui admin
            $table->string('sandi_baru')->nullable();
            $table->timestamp('sandi_diminta_at')->nullable();
        });

        // Dulu login memakai kolom name; jadikan nama pengguna awal akun lama.
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $u) {
            $nama = Str::slug($u->name, '.') ?: 'pengguna';
            $dipakai = DB::table('users')->where('username', $nama)->exists();
            DB::table('users')->where('id', $u->id)->update(['username' => $dipakai ? "$nama.$u->id" : $nama]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->unique()->change();
            $table->string('email')->nullable()->change(); // akun baru cukup nama pengguna
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'akses_tambah', 'akses_edit', 'terakhir_aktif_at', 'sandi_baru', 'sandi_diminta_at']);
        });
    }
};
