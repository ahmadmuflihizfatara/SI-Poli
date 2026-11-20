<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit WSTG-IDNT-01/03: akun dinonaktifkan (bukan dihapus) agar atribusi data medis & log tetap utuh,
 * akun yang sandinya ditetapkan admin wajib menggantinya, dan role bawaan menjadi role berprivilese terendah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('nonaktif_at')->nullable()->after('role');
            $table->boolean('wajib_ganti_sandi')->default(false)->after('password');
            $table->enum('role', ['admin', 'perawat', 'psikolog', 'dokter', 'asisten_kesehatan', 'pengasuh'])->default('pengasuh')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nonaktif_at', 'wajib_ganti_sandi']);
            $table->enum('role', ['admin', 'perawat', 'psikolog', 'dokter', 'asisten_kesehatan', 'pengasuh'])->default('perawat')->change();
        });
    }
};
