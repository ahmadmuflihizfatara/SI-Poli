<?php

namespace Tests\Feature;

use App\Models\KejadianLuarBiasa;
use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KejadianLuarBiasaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_kejadian_luar_biasa_terpisah_dari_laporan_kesehatan(): void
    {
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($perawat)->get(route('dashboard'))->assertSee('Kejadian Luar Biasa');
        $this->get(route('klb.index'))->assertOk()->assertSee('Belum ada kejadian luar biasa');
        $this->get(route('klb.index', ['tambah' => 1]))->assertSee('Informasi Kejadian Luar Biasa');

        $this->post(route('klb.store'), ['nama' => 'Diare Massal Asrama Putra', 'deskripsi' => 'Diare akut setelah makan malam'])->assertRedirect();
        $klb = KejadianLuarBiasa::sole();
        $this->assertSame('Berlangsung', $klb->status);

        $keluhan = [
            'nama' => 'Rahadian Ronggo', 'npm' => '2201099', 'kelas' => 'I RKS A', 'tingkat' => 'I', 'jenis_kelamin' => 'Laki-laki',
            'kamar' => 'A101', 'keluhan' => 'Diare 5 kali', 'terapi' => "Oralit\nZinc 20 mg", 'status' => 'Sedang',
            'tanggal_kontrol_selanjutnya' => today()->addDays(3)->toDateString(),
        ];
        // Rute "tambah" tidak boleh tertangkap sebagai {keluhan}
        $this->get(route('klb.keluhan.create', $klb))->assertOk()->assertSee('Tambah Keluhan Baru');
        $this->post(route('klb.keluhan.store', $klb), $keluhan)->assertRedirect(route('klb.show', $klb));
        $k = $klb->keluhan()->sole();

        $this->post(route('klb.keluhan.kontrol', [$klb, $k]), ['hasil_kontrol' => 'Diare berkurang'])->assertRedirect();
        $this->get(route('klb.show', $klb))->assertSee('Rahadian Ronggo')->assertSee('Diare berkurang')->assertSee('Kejadian Luar Biasa Selesai');
        $this->get(route('klb.keluhan.show', [$klb, $k]))->assertOk()->assertSee('Zinc 20 mg')->assertSee('Riwayat Evaluasi Kontrol');

        // Terpisah dari laporan kesehatan utama
        $this->assertSame(0, Keluhan::count());

        $this->patch(route('klb.selesai', $klb))->assertRedirect();
        $this->assertTrue($klb->fresh()->selesai());
        $this->post(route('klb.keluhan.store', $klb), $keluhan)->assertForbidden();
        $this->get(route('klb.show', $klb))->assertDontSee('Tambah Keluhan')->assertDontSee('data-konfirmasi', false);

        // Keluhan dari KLB lain tidak bisa dibuka lewat URL KLB ini
        $lain = KejadianLuarBiasa::create(['nama' => 'Influenza', 'deskripsi' => 'Demam']);
        $this->get(route('klb.keluhan.show', [$lain, $k]))->assertNotFound();

        $this->assertSame(['Tambah', 'Tambah', 'Edit', 'Edit'], LogAktivitas::orderBy('id')->pluck('aksi')->all());
        $this->assertSame(['Kejadian Luar Biasa'], LogAktivitas::distinct()->pluck('sumber_daya')->all());

        // Akun tanpa akses Tambah/Edit hanya bisa melihat
        $lihat = User::factory()->create(['role' => 'perawat', 'akses_tambah' => false, 'akses_edit' => false]);
        $this->actingAs($lihat)->get(route('klb.index', ['tambah' => 1]))->assertOk()->assertDontSee('Informasi Kejadian Luar Biasa');
        $this->post(route('klb.store'), ['nama' => 'X', 'deskripsi' => 'Y'])->assertForbidden();
        $this->patch(route('klb.selesai', $lain))->assertForbidden();
    }
}
