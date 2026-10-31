<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\PegawaiSeeder;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKesehatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kontrol_terlambat_ditandai_dan_diurutkan_paling_atas(): void
    {
        $this->seed(TarunaSeeder::class);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan'];
        Keluhan::create($dasar + ['keluhan' => 'Kontrol minggu depan', 'tanggal_kontrol_selanjutnya' => today()->addWeek()]);
        Keluhan::create($dasar + ['keluhan' => 'Kontrol bulan lalu', 'tanggal_kontrol_selanjutnya' => today()->subDays(29)]);
        Keluhan::create($dasar + ['keluhan' => 'Kontrol hari ini', 'tanggal_kontrol_selanjutnya' => today()]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSeeInOrder(['Kontrol bulan lalu', 'Terlambat 29 hari', 'Kontrol hari ini', 'Hari ini', 'Kontrol minggu depan'])
            ->assertDontSee('title="Kontrol bulan lalu"', false);
    }

    public function test_pasien_sembuh_tidak_dianggap_terlambat(): void
    {
        $this->seed(TarunaSeeder::class);
        Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan',
            'keluhan' => 'Sudah pulih', 'tanggal_kontrol_selanjutnya' => today()->subDays(3), 'status_pemulihan' => 'Sembuh',
        ]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSee('Sudah pulih')->assertDontSee('Terlambat 3 hari');
    }

    public function test_popup_ringkas_memuat_info_taruna_dan_aksi_sesuai_izin(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        $keluhan = Keluhan::create([
            'taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'terapi' => 'Kompres', 'status' => 'Sedang',
            'keluhan' => 'Keseleo', 'tanggal_kontrol_selanjutnya' => today()->addDay(),
        ]);
        $info = fn ($res) => json_decode(html_entity_decode(str($res->getContent())->match('/data-info="([^"]+)"/')), true);

        $admin = User::factory()->create(['role' => 'admin']);
        $data = $info($this->actingAs($admin)->get(route('laporan-kesehatan.index'))->assertOk()->assertSee('id="lk-pop"', false));
        $this->assertSame([$taruna->kamar, 'Keseleo', 'Kompres'], [$data['kamar'], $data['keluhan'], $data['terapi']]);
        $this->assertSame(route('laporan-kesehatan.toggle-sembuh', $keluhan), $data['sembuh']);
        $this->assertSame(route('laporan-kesehatan.show', $keluhan).'#catat-kontrol', $data['edit']);

        $keluhan->update(['status_pemulihan' => 'Sembuh']);
        $data = $info($this->actingAs($admin)->get(route('laporan-kesehatan.index')));
        $this->assertNull($data['sembuh']);
        $this->assertSame('Sudah sembuh', $data['alasan']);
    }

    public function test_toolbar_punya_cari_npm_filter_ganda_reset_dan_konfirmasi_sembuh(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        Keluhan::create([
            'taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'terapi' => 'Obat', 'status' => 'Berat',
            'keluhan' => 'Demam', 'tanggal_kontrol_selanjutnya' => today()->subDay(),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSee('placeholder="Cari nama atau NPM"', false)
            ->assertSee('data-npm="'.$taruna->npm.'"', false)
            ->assertSee('data-kontrol="terlambat"', false)
            ->assertSee('name="saring-status"', false)
            ->assertSee('name="saring-tingkat"', false)
            ->assertSee('id="reset-saring"', false)
            ->assertSee('id="konfirmasi-sembuh"', false)
            ->assertDontSee('<select', false);
    }

    public function test_keluhan_taruna_yang_sama_selalu_bersusun(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b] = Taruna::take(2)->get();
        $dasar = ['tanggal_awal' => today(), 'terapi' => 'Obat', 'status' => 'Ringan'];
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'keluhan' => 'A dekat', 'tanggal_kontrol_selanjutnya' => today()->addDay()]);
        Keluhan::create($dasar + ['taruna_id' => $b->id, 'keluhan' => 'B tengah', 'tanggal_kontrol_selanjutnya' => today()->addDays(5)]);
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'keluhan' => 'A jauh', 'tanggal_kontrol_selanjutnya' => today()->addDays(10)]);

        // tanpa pengelompokan urutannya A dekat, B tengah, A jauh
        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSeeInOrder(['A dekat', 'A jauh', 'B tengah']);
    }

    public function test_tambah_keluhan_menyimpan_waktu_laporan_obat_terstruktur_dan_pemeriksa(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->travelTo(now()->setDate(2026, 10, 31)->setTime(15, 0));
        $perawat = User::factory()->create(['name' => 'Rina Perawat']);

        $this->actingAs($perawat)->post(route('laporan-kesehatan.store'), [
            'taruna_id' => Taruna::first()->id, 'waktu_laporan' => '2026-10-30T07:45',
            'tekanan_darah' => '120/80', 'suhu' => 38, 'nadi' => 90, 'keluhan' => 'Demam', 'status' => 'Berat',
            'obat' => [
                ['nama' => 'Paracetamol 500 mg', 'aturan' => '3×1 sesudah makan'],
                ['nama' => 'Vitamin C', 'aturan' => ''],
            ],
            'tanggal_kontrol_selanjutnya' => '2026-10-31',
        ])->assertRedirect(route('laporan-kesehatan.index'));

        $this->get(route('laporan-kesehatan.create'))->assertOk()->assertDontSee('name="keterangan"', false);
        $keluhan = Keluhan::sole();
        $this->assertSame("Paracetamol 500 mg · 3×1 sesudah makan\nVitamin C", $keluhan->terapi);
        $this->assertSame(['2026-10-30', '2026-10-30 07:45'], [$keluhan->tanggal_awal->toDateString(), $keluhan->waktu_laporan->format('Y-m-d H:i')]);

        $this->get(route('laporan-kesehatan.index'))->assertSeeInOrder(['Pegawai pemeriksa', 'Rina Perawat']);
        $this->get(route('laporan-kesehatan.show', $keluhan))->assertOk()
            ->assertSeeInOrder(['Dalam perawatan', 'Identitas', 'Data Klinis', '30 Okt 2026, 07.45', 'Berat', '31 Okt 2026', 'Hari ini', 'Rina Perawat'])
            ->assertSeeInOrder(['<th scope="col">Terapi/Obat</th>', '<th scope="col">Aturan Pakai</th>', '<td>Paracetamol 500 mg</td>', '<td>3×1 sesudah makan</td>', '<td>Vitamin C</td>', '<td>-</td>'], false)
            ->assertDontSee('name="obat[0][dosis]"', false)
            ->assertDontSee('name="obat[0][durasi]"', false)
            ->assertSee('Nyatakan '.Taruna::first()->nama.' sembuh?')
            ->assertSee('Catat Kontrol')
            ->assertSee('id="catat-kontrol"', false);
    }

    public function test_catat_kontrol_dari_popup_mencatat_pemeriksa_dan_waktu_laporan(): void
    {
        $this->seed([TarunaSeeder::class, PegawaiSeeder::class]);
        $dokter = Pegawai::where('jabatan', 'Dokter')->first();
        $this->travelTo(now()->setDate(2026, 10, 31)->setTime(9, 15));
        $keluhan = Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Paracetamol', 'status' => 'Ringan',
            'keluhan' => 'Demam', 'tanggal_kontrol_selanjutnya' => today(),
        ]);
        $perawat = User::factory()->create(['name' => 'Sinta Perawat']);

        $this->actingAs($perawat)->post(route('laporan-kesehatan.kontrol.update', $keluhan), [
            'hasil_pemeriksaan' => 'Suhu normal', 'pemeriksa_id' => $dokter->id, 'obat' => [['nama' => 'Paracetamol', 'aturan' => 'Bila demam']], 'perlu_rujukan' => '0',
            'tanggal_kontrol_selanjutnya' => '2026-11-02',
        ])->assertRedirect(route('laporan-kesehatan.show', $keluhan));

        $kontrol = $keluhan->riwayatKontrol()->sole();
        $this->assertSame([$perawat->id, $dokter->id], [$kontrol->created_by, $kontrol->pemeriksa_id]);
        $this->assertSame('Paracetamol · Bila demam', $keluhan->fresh()->terapi);
        $this->get(route('laporan-kesehatan.show', $keluhan))->assertOk()
            ->assertSeeInOrder(['Waktu Laporan', 'Pegawai Pemeriksa', '31 Okt 2026', '09.15', 'Suhu normal', $dokter->nama]);
    }

    public function test_catat_kontrol_wajib_memilih_pemeriksa_dari_daftar_pegawai(): void
    {
        $this->seed([TarunaSeeder::class, PegawaiSeeder::class]);
        $keluhan = Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Paracetamol', 'status' => 'Ringan',
            'keluhan' => 'Demam', 'tanggal_kontrol_selanjutnya' => today(),
        ]);
        $isian = ['hasil_pemeriksaan' => 'Membaik', 'obat' => [['nama' => 'Paracetamol']], 'perlu_rujukan' => '0', 'tanggal_kontrol_selanjutnya' => today()->toDateString()];

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.show', $keluhan))->assertOk()
            ->assertSeeInOrder(['Hasil Kontrol', 'Nama pemeriksa', 'data-grup="Dokter">Dokter</li>', Pegawai::where('jabatan', 'Dokter')->value('nama'), 'data-grup="Perawat">Perawat</li>'], false);
        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian)->assertSessionHasErrors('pemeriksa_id');
        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian + ['pemeriksa_id' => 999])->assertSessionHasErrors('pemeriksa_id');
        $this->assertSame(0, $keluhan->riwayatKontrol()->count());
    }

    public function test_waktu_minum_obat_tersimpan_dan_lainnya_wajib_diberi_keterangan(): void
    {
        $this->seed([TarunaSeeder::class, PegawaiSeeder::class]);
        $keluhan = Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Paracetamol', 'status' => 'Ringan',
            'keluhan' => 'Demam', 'tanggal_kontrol_selanjutnya' => today(),
        ]);
        $isian = ['hasil_pemeriksaan' => 'Membaik', 'pemeriksa_id' => Pegawai::first()->id, 'perlu_rujukan' => '0', 'tanggal_kontrol_selanjutnya' => today()->toDateString()];
        $this->actingAs(User::factory()->create());

        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian + ['obat' => [['nama' => 'Ibuprofen', 'waktu' => 'Lainnya']]])
            ->assertSessionHasErrors('obat.0.waktu_lain');
        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian + ['obat' => [
            ['nama' => 'Paracetamol', 'aturan' => '3×1', 'waktu' => 'Sesudah makan'],
            ['nama' => 'Ibuprofen', 'waktu' => 'Lainnya', 'waktu_lain' => 'Saat nyeri'],
            ['nama' => 'Istirahat'],
        ]])->assertSessionHasNoErrors();

        $this->assertSame("Paracetamol · 3×1 · Sesudah makan\nIbuprofen · - · Saat nyeri\nIstirahat", $keluhan->fresh()->terapi);
        $this->get(route('laporan-kesehatan.show', $keluhan))
            ->assertSeeInOrder(['<th scope="col">Waktu Minum</th>', '<td>Paracetamol</td>', '<td>3×1</td>', '<td>Sesudah makan</td>', '<td>Ibuprofen</td>', '<td>-</td>', '<td>Saat nyeri</td>'], false)
            // isian popup terisi ulang: keterangan selain pilihan baku tampil sebagai "Lainnya"
            ->assertSee('name="obat[1][waktu]" value="Lainnya" data-waktu-obat checked', false)
            ->assertSee('value="Saat nyeri"', false);
    }

    public function test_tempat_rujukan_opsional_saat_kontrol_dan_bisa_diisi_kemudian(): void
    {
        $this->seed([TarunaSeeder::class, PegawaiSeeder::class]);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Paracetamol', 'status' => 'Berat', 'tanggal_kontrol_selanjutnya' => today()];
        $keluhan = Keluhan::create($dasar + ['keluhan' => 'Patah tulang']);
        $lain = Keluhan::create($dasar + ['keluhan' => 'Demam']);
        $isian = ['hasil_pemeriksaan' => 'Perlu rontgen', 'pemeriksa_id' => Pegawai::first()->id, 'obat' => [['nama' => 'Paracetamol']], 'tanggal_kontrol_selanjutnya' => today()->toDateString()];
        $this->actingAs(User::factory()->create(['role' => 'perawat']));

        // dirujuk tanpa tempat (belum jelas), lalu tidak dirujuk: tempat yang terkirim dibuang
        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian + ['perlu_rujukan' => '1'])->assertSessionHasNoErrors();
        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), $isian + ['perlu_rujukan' => '0', 'tempat_rujukan' => 'RSUD'])->assertSessionHasNoErrors();
        [$dirujuk, $tidak] = $keluhan->riwayatKontrol()->orderBy('id')->get();
        $this->assertSame([true, null, false, null], [$dirujuk->perlu_rujukan, $dirujuk->tempat_rujukan, $tidak->perlu_rujukan, $tidak->tempat_rujukan]);
        $this->get(route('laporan-kesehatan.show', $keluhan))->assertSeeInOrder(['Rujukan', 'Dirujuk', 'Tempat belum diisi']);

        $this->patch(route('laporan-kesehatan.kontrol.rujukan', [$keluhan, $dirujuk]), ['tempat_rujukan' => 'RSUD Kota Bogor'])
            ->assertRedirect(route('laporan-kesehatan.show', $keluhan));
        $this->assertSame('RSUD Kota Bogor', $dirujuk->fresh()->tempat_rujukan);
        $this->assertStringContainsString('diisi RSUD Kota Bogor', LogAktivitas::latest('id')->value('pesan'));
        $this->get(route('laporan-kesehatan.show', $keluhan))->assertSeeInOrder(['Dirujuk', 'RSUD Kota Bogor'])->assertDontSee('Tempat belum diisi');

        // kontrol yang tidak dirujuk atau milik keluhan lain tidak bisa diberi tempat rujukan
        $this->patch(route('laporan-kesehatan.kontrol.rujukan', [$keluhan, $tidak]), ['tempat_rujukan' => 'RSUD'])->assertNotFound();
        $this->patch(route('laporan-kesehatan.kontrol.rujukan', [$lain, $dirujuk]), ['tempat_rujukan' => 'RSUD'])->assertNotFound();
        $this->patch(route('laporan-kesehatan.kontrol.rujukan', [$keluhan, $dirujuk]), ['tempat_rujukan' => ''])->assertSessionHasErrors('tempat_rujukan');
    }

    public function test_status_pemulihan_tampil_sebagai_penanda_pekat_tanpa_titik(): void
    {
        $this->seed(TarunaSeeder::class);
        $keluhan = Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Obat', 'status' => 'Ringan',
            'keluhan' => 'Pusing', 'tanggal_kontrol_selanjutnya' => today()->addDay(),
        ]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.show', $keluhan))->assertOk()
            ->assertSee('<span class="ik-tanda ik-tanda--lg ik-tanda--rawat">', false)
            ->assertDontSee('pl-badge pl-badge-lg', false);
    }

    public function test_waktu_laporan_tidak_boleh_di_masa_depan(): void
    {
        $this->seed(TarunaSeeder::class);

        $this->actingAs(User::factory()->create())->post(route('laporan-kesehatan.store'), [
            'taruna_id' => Taruna::first()->id, 'waktu_laporan' => now()->addHour()->format('Y-m-d\TH:i'),
            'tekanan_darah' => '120/80', 'suhu' => 37, 'nadi' => 80, 'keluhan' => 'Pusing', 'status' => 'Ringan',
            'obat' => [['nama' => 'Istirahat']], 'tanggal_kontrol_selanjutnya' => today()->toDateString(),
        ])->assertSessionHasErrors(['waktu_laporan' => 'Waktu laporan tidak boleh melewati waktu sekarang.']);

        $this->assertSame(0, Keluhan::count());
    }

    public function test_detail_menandai_kontrol_terlambat_dan_terapi_lama_tetap_terbaca(): void
    {
        $this->seed(TarunaSeeder::class);
        $keluhan = Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today()->subDays(5), 'terapi' => 'Kompres hangat, istirahat', 'status' => 'Sedang',
            'keluhan' => 'Keseleo', 'tanggal_kontrol_selanjutnya' => today()->subDays(2),
        ]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.show', $keluhan))->assertOk()
            ->assertSee('Terlambat 2 hari')
            ->assertSee('<td>Kompres hangat, istirahat</td>', false)
            ->assertDontSee('Keterangan Lainnya')
            ->assertDontSee('name="keterangan"', false)
            ->assertDontSee('>No<', false);
    }
}
