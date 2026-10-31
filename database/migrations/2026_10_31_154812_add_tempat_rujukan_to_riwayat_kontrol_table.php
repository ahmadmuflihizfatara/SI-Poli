<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            // diisi bila perlu_rujukan; boleh kosong dulu dan dilengkapi setelah tempatnya jelas
            $table->string('tempat_rujukan', 150)->nullable()->after('perlu_rujukan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            $table->dropColumn('tempat_rujukan');
        });
    }
};
