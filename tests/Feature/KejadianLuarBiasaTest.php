<?php

namespace Tests\Feature;

use App\Models\KejadianLuarBiasa;
use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\PegawaiSeeder;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KejadianLuarBiasaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_kejadian_luar_biasa_terpisah_dari_laporan_kesehatan(): void
    {
        $this->seed([TarunaSeeder::class, PegawaiSeeder::class]);
        $taruna = Taruna::first();
        $pemeriksa = Pegawai::where('jabatan', 'Dokter')->first();
        $dokter = User::factory()->create(['role' => 'dokter']);

        $this->actingAs($dokter)->get(route('dashboard'))->assertSee('Kejadian Luar Biasa');
        $this->get(route('klb.index'))->assertOk()->assertSee('Belum ada kejadian luar biasa');
        $this->get(route('klb.index', ['tambah' => 1]))->assertSee('id="dialog-tambah"', false);

        $this->post(route('klb.store'), ['nama' => 'Diare Massal Asrama Putra', 'deskripsi' => 'Diare akut setelah makan malam'])->assertRedirect();
        $klb = KejadianLuarBiasa::sole();
        $this->assertSame('Berlangsung', $klb->status);

        // isian sama dengan Tambahkan Keluhan Baru di Laporan Kesehatan, tanpa tingkat keparahan
        $keluhan = [
            'taruna_id' => $taruna->id, 'pemeriksa_id' => $pemeriksa->id, 'waktu_laporan' => now()->format('Y-m-d\TH:i'),
            'sistolik' => 120, 'diastolik' => 80, 'suhu' => 38, 'nadi' => 90, 'keluhan' => 'Diare 5 kali',
            'obat' => [['nama' => 'Oralit'], ['nama' => 'Zinc 20 mg', 'aturan' => '1x1', 'waktu' => 'Sesudah makan']],
            'tanggal_kontrol_selanjutnya' => today()->addDays(3)->toDateString(),
        ];
        // Rute "tambah" tidak boleh tertangkap sebagai {keluhan}
        // Informasi umum dipilih dari data taruna di database, lalu disalin ke keluhan KLB
        $this->get(route('klb.keluhan.create', $klb))->assertOk()->assertSee('Tambahkan Keluhan Baru')->assertSee($taruna->nama)
            ->assertSee(route('klb.keluhan.store', $klb))->assertDontSee('name="status"', false);
        $this->post(route('klb.keluhan.store', $klb), ['taruna_id' => 999] + $keluhan)->assertSessionHasErrors('taruna_id');
        $this->post(route('klb.keluhan.store', $klb), ['tanggal_kontrol_selanjutnya' => null] + $keluhan)->assertSessionHasErrors('tanggal_kontrol_selanjutnya');
        $this->post(route('klb.keluhan.store', $klb), $keluhan)->assertSessionHasNoErrors()->assertRedirect(route('klb.show', $klb));
        $k = $klb->keluhan()->sole();
        $this->assertSame($taruna->only(['nama', 'npm', 'kelas', 'tingkat', 'jenis_kelamin', 'kamar']), $k->only(['nama', 'npm', 'kelas', 'tingkat', 'jenis_kelamin', 'kamar']));
        $this->assertSame(['120/80', $pemeriksa->id, null], [$k->tekanan_darah, $k->pemeriksa_id, $k->status]);
        $this->assertSame("Oralit\nZinc 20 mg · 1x1 · Sesudah makan", $k->terapi);
        $this->assertSame('Diare 5 kali', $k->kontrol()->sole()->hasil_kontrol); // kontrol awal saat keluhan dilaporkan

        $kontrol = ['hasil_pemeriksaan' => 'Diare berkurang', 'pemeriksa_id' => $pemeriksa->id, 'obat' => [['nama' => 'Oralit']], 'perlu_rujukan' => '1',
            'tempat_rujukan' => '', 'tanggal_kontrol_selanjutnya' => today()->addDay()->toDateString(), 'sistolik' => 118, 'diastolik' => 76, 'suhu' => 37, 'nadi' => 80];
        $this->post(route('klb.keluhan.kontrol', [$klb, $k]), $kontrol)->assertRedirect(route('klb.keluhan.show', [$klb, $k]));
        $terakhir = $k->kontrolTerakhir()->sole();
        $this->assertSame(['Oralit', today()->addDay()->toDateString()], [$k->fresh()->terapi, $k->fresh()->tanggal_kontrol_selanjutnya->toDateString()]);
        $this->patch(route('klb.keluhan.kontrol.rujukan', [$klb, $k, $terakhir]), ['tempat_rujukan' => 'RSUD Kota'])->assertRedirect();
        $this->assertSame('RSUD Kota', $terakhir->fresh()->tempat_rujukan);
        $this->get(route('klb.show', $klb))->assertSee($taruna->nama)->assertSee('Diare berkurang')->assertSee('Kejadian Luar Biasa Selesai');
        $this->get(route('klb.keluhan.show', [$klb, $k]))->assertOk()->assertViewIs('laporan-kesehatan.detail-keluhan')
            ->assertSee('Informasi Keluhan')->assertSee('Zinc 20 mg')->assertSee('Riwayat Kontrol')->assertSee('Catat Kontrol')->assertSee('RSUD Kota')
            ->assertSee(route('klb.show', $klb))->assertDontSee('Nyatakan Sembuh')->assertDontSee('<dt>Tingkat keparahan</dt>', false);

        // Terpisah dari laporan kesehatan utama
        $this->assertSame(0, Keluhan::count());

        $this->patch(route('klb.selesai', $klb))->assertRedirect();
        $this->assertTrue($klb->fresh()->selesai());
        $this->post(route('klb.keluhan.store', $klb), $keluhan)->assertForbidden();
        $this->get(route('klb.show', $klb))->assertDontSee('Tambah Keluhan')->assertDontSee('data-konfirmasi', false);
        $this->get(route('klb.keluhan.show', [$klb, $k]))->assertOk()->assertDontSee('Catat Kontrol'); // KLB selesai: baca saja
        $this->post(route('klb.keluhan.kontrol', [$klb, $k]), $kontrol)->assertForbidden();

        // Keluhan dari KLB lain tidak bisa dibuka lewat URL KLB ini
        $lain = KejadianLuarBiasa::create(['nama' => 'Influenza', 'deskripsi' => 'Demam']);
        $this->get(route('klb.keluhan.show', [$lain, $k]))->assertNotFound();

        $this->assertSame(['Tambah', 'Tambah', 'Edit', 'Edit', 'Edit'], LogAktivitas::orderBy('id')->pluck('aksi')->all());
        $this->assertSame(['Kejadian Luar Biasa'], LogAktivitas::distinct()->pluck('sumber_daya')->all());

        $perawat = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($perawat)->get(route('klb.index'))->assertOk()->assertDontSee('Tambah Kejadian Luar Biasa Baru');
        $this->get(route('klb.show', $klb))->assertOk();
        $this->post(route('klb.store'), ['nama' => 'Tidak Diizinkan', 'deskripsi' => 'Perawat tidak dapat membuat KLB'])->assertForbidden();
        $this->patch(route('klb.selesai', $lain))->assertRedirect();
        $this->assertTrue($lain->fresh()->selesai());

        // Akun tanpa akses Tambah/Edit hanya bisa melihat
        $lihat = User::factory()->create(['role' => 'perawat', 'akses_tambah' => false, 'akses_edit' => false]);
        $this->actingAs($lihat)->get(route('klb.index', ['tambah' => 1]))->assertOk()->assertDontSee('id="dialog-tambah"', false);
        $this->post(route('klb.store'), ['nama' => 'X', 'deskripsi' => 'Y'])->assertForbidden();
        $this->patch(route('klb.selesai', $lain))->assertForbidden();
    }

    public function test_hanya_admin_dan_dokter_yang_dapat_membuat_klb(): void
    {
        foreach (['perawat', 'psikolog'] as $role) {
            $pengguna = User::factory()->create(['role' => $role]);

            $this->actingAs($pengguna)
                ->post(route('klb.store'), ['nama' => "KLB {$role}", 'deskripsi' => 'Tidak berwenang'])
                ->assertForbidden();
        }

        foreach (['admin', 'dokter'] as $role) {
            $pengguna = User::factory()->create(['role' => $role]);

            $this->actingAs($pengguna)
                ->get(route('klb.index', ['tambah' => 1]))
                ->assertOk()
                ->assertSee('id="dialog-tambah"', false);
            $this->post(route('klb.store'), ['nama' => "KLB {$role}", 'deskripsi' => 'Berwenang'])->assertRedirect();
        }
    }
}
