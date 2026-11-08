<?php

namespace Tests\Feature;

use App\Models\KegiatanPemeriksaan;
use App\Models\LogAktivitas;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemeriksaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kartu_mptb_dan_samapta_membuka_popup_tambah_pemeriksaan_yang_mulai_hari_ini(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'dokter']));
        $senior = Taruna::where('tingkat', 'II')->first();

        $this->get(route('pemeriksaan-kesehatan.index'))->assertOk()
            ->assertSee('data-tambah="MPTB"', false)->assertSee('data-tambah="Samapta"', false)
            ->assertSee('Nama Pemeriksaan Kesehatan')->assertDontSee('Tanggal selesai');

        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => ''])->assertSessionHasErrors('nama');
        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('data-buka="Samapta"', false); // popup terbuka lagi setelah gagal validasi
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I', 'keterangan' => 'Semester ganjil'])
            ->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan = KegiatanPemeriksaan::sole()));

        $mulai = today()->locale('id')->translatedFormat('j F Y');
        $this->assertSame(['Samapta', 'Samapta I', today()->toDateString(), null], [$kegiatan->jenis, $kegiatan->nama, $kegiatan->tanggal_mulai->toDateString(), $kegiatan->tanggal_selesai]);
        $this->assertSame('Semester ganjil', $kegiatan->keterangan);
        $this->assertSame("Pemeriksaan Samapta I ($mulai – sekarang) ditambahkan.", LogAktivitas::sole()->pesan);
        $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('Samapta I')->assertSee($senior->nama)
            ->assertSee('data-tingkat="II" aria-pressed', false) // tombol pindah tingkat
            ->assertSee('Selesaikan pemeriksaan')->assertDontSee('Keluhan sesi sebelumnya');
        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('Samapta I')->assertSee("$mulai – sekarang")->assertSee('Aktif');
    }

    public function test_mptb_bisa_dimulai_mundur_dan_tanggal_sebelumnya_bisa_dibuka_serta_diisi(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'dokter']));
        $taruna = Taruna::where('tingkat', 'I')->first();
        $mulai = today()->subDays(3)->toDateString();

        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'MPTB', 'nama' => 'MPTB Mundur', 'tanggal_mulai' => $mulai])
            ->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan = KegiatanPemeriksaan::sole()));
        $this->assertSame($mulai, $kegiatan->tanggal_mulai->toDateString());

        $this->get(route('pemeriksaan-kesehatan.show', ['kegiatan' => $kegiatan, 'tanggal' => $mulai, 'sesi' => 'Pagi']))->assertOk()
            ->assertSee('data-tanggal="'.$mulai.'"', false);
        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['tanggal' => $mulai, 'sesi' => 'Pagi', 'data' => [$taruna->id => ['nadi' => '90']]])
            ->assertSessionHasNoErrors();
        $this->assertSame(90, $kegiatan->pemeriksaan()->where('periode', $mulai)->sole()->nadi);

        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['tanggal' => today()->subDays(4)->toDateString(), 'sesi' => 'Pagi', 'data' => [$taruna->id => ['nadi' => '90']]])
            ->assertSessionHasErrors('tanggal'); // sebelum tanggal mulai
    }

    public function test_tanggal_mulai_mptb_wajib_dan_tidak_boleh_melewati_hari_ini(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'dokter']));

        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'MPTB', 'nama' => 'MPTB', 'tanggal_mulai' => today()->addDay()->toDateString()])
            ->assertSessionHasErrors(['tanggal_mulai' => 'Tanggal mulai tidak boleh melewati hari ini.']);
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'MPTB', 'nama' => 'MPTB'])->assertSessionHasErrors('tanggal_mulai');
        $this->assertSame(0, KegiatanPemeriksaan::count());
    }

    public function test_samapta_tetap_mulai_hari_ini_walau_tanggal_mulai_dikirim(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'dokter']));

        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()->subDays(3)->toDateString()])
            ->assertSessionHasNoErrors();

        $this->assertSame(today()->toDateString(), KegiatanPemeriksaan::sole()->tanggal_mulai->toDateString());
    }

    public function test_perawat_bisa_melihat_tetapi_tidak_bisa_menambah_pemeriksaan_dan_dokter_bisa_mengakses(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::where('tingkat', 'I')->first();
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()]);
        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36.5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada'];

        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->get(route('pemeriksaan-kesehatan.index'))->assertOk()->assertDontSee('data-tambah="MPTB"', false)
            ->assertDontSee('Mulai Pemeriksaan Baru')->assertSee('MPTB 2026'); // tanpa kartu buat rikkes, riwayat tetap tampil
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I'])->assertForbidden();
        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['tanggal' => today()->toDateString(), 'sesi' => 'Pagi', 'data' => [$taruna->id => $vital]])->assertRedirect();
        $this->assertSame(0, Pemeriksaan::count()); // hasil baru dilewati

        $this->actingAs(User::factory()->create(['role' => 'dokter']))
            ->get(route('pemeriksaan-kesehatan.index'))->assertOk()->assertSee('data-tambah="MPTB"', false)->assertSee('Mulai Pemeriksaan Baru');
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I'])->assertRedirect();
        $this->assertSame(2, KegiatanPemeriksaan::count());
    }

    public function test_pengasuh_hanya_melihat_rikkes_aktif_tanpa_bisa_menambah_atau_mengubah_hasil(): void
    {
        $this->seed(TarunaSeeder::class);
        [$taruna, $tarunaLain] = Taruna::where('tingkat', 'I')->take(2)->get();
        $aktif = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB Aktif', 'tanggal_mulai' => today()]);
        $selesai = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta Lama', 'tanggal_mulai' => today()->subDays(5), 'tanggal_selesai' => today()->subDay()]);
        $sesi = ['tanggal' => today()->toDateString(), 'sesi' => 'Pagi'];
        $hasil = Pemeriksaan::create(['taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $aktif->id, 'jenis' => 'MPTB', 'periode' => $sesi['tanggal'], 'sesi' => 'Pagi', 'nadi' => 80]);

        $this->actingAs(User::factory()->create(['role' => 'pengasuh']));
        $this->get(route('pemeriksaan-kesehatan.index'))->assertOk()->assertSee('MPTB Aktif')->assertDontSee('Samapta Lama')->assertDontSee('Mulai Pemeriksaan Baru');
        $this->get(route('pemeriksaan-kesehatan.show', $aktif))->assertOk()->assertDontSee('Ekspor PDF')->assertDontSee('Selesaikan pemeriksaan')
            ->assertDontSee('Simpan perubahan')->assertSee('Akun Anda hanya bisa melihat hasil pemeriksaan.');
        $this->get(route('pemeriksaan-kesehatan.detail', [$aktif, $taruna]))->assertOk();
        $this->get(route('pemeriksaan-kesehatan.show', $selesai))->assertNotFound();
        $this->get(route('pemeriksaan-kesehatan.detail', [$selesai, $taruna]))->assertNotFound();
        $this->get(route('pemeriksaan-kesehatan.ekspor.pdf', $aktif))->assertForbidden();
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta II'])->assertForbidden();

        $this->post(route('pemeriksaan-kesehatan.simpan', $aktif), $sesi + ['data' => [$taruna->id => ['nadi' => '95'], $tarunaLain->id => ['nadi' => '70']]])
            ->assertForbidden();
        $this->assertSame(80, $hasil->refresh()->nadi);
        $this->assertSame(1, Pemeriksaan::count());
    }

    public function test_pemeriksaan_selesai_lewat_tombol_dan_setelahnya_tidak_bisa_diisi(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()->subDays(3)]);
        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36.5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada'];

        $this->actingAs(User::factory()->create(['role' => 'pengasuh']))
            ->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertDontSee('Selesaikan pemeriksaan');
        $this->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertForbidden();
        $this->assertNull($kegiatan->refresh()->tanggal_selesai);

        $this->actingAs(User::factory()->create(['role' => 'dokter']))
            ->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan));
        $this->assertSame(today()->toDateString(), $kegiatan->refresh()->tanggal_selesai->toDateString());
        $this->assertStringContainsString('dinyatakan selesai', LogAktivitas::sole()->pesan);

        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['data' => [$taruna->id => $vital]])->assertSessionHasErrors('kegiatan');
        $this->assertSame(0, Pemeriksaan::count());
        $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('sudah selesai')->assertDontSee('Selesaikan pemeriksaan');
        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('Selesai');
    }

    public function test_admin_bisa_menyelesaikan_pemeriksaan(): void
    {
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertRedirect();

        $this->assertTrue($kegiatan->refresh()->selesai());
    }

    public function test_dokter_dan_admin_bisa_mengaktifkan_kembali_mptb_yang_selesai_dan_mengisinya_lagi(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::where('tingkat', 'I')->first();

        foreach (['dokter', 'admin'] as $role) {
            $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => "MPTB $role", 'tanggal_mulai' => today()->subDays(3), 'tanggal_selesai' => today()->subDay()]);
            $hasil = Pemeriksaan::create(['taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $kegiatan->id, 'jenis' => 'MPTB', 'periode' => today()->subDay()->toDateString(), 'sesi' => 'Pagi', 'nadi' => 80]);

            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()
                ->assertSee('data-konfirmasi="konfirmasi-aktifkan"', false)->assertSee('Apakah Anda yakin ingin mengaktifkan kembali');

            $this->patch(route('pemeriksaan-kesehatan.aktifkan', $kegiatan))->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan));

            $this->assertFalse($kegiatan->refresh()->selesai());
            $this->assertModelExists($hasil);
            $this->assertStringContainsString('diaktifkan kembali', LogAktivitas::latest('id')->first()->pesan);
            $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('Selesaikan pemeriksaan')->assertDontSee('konfirmasi-aktifkan');

            $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['tanggal' => today()->toDateString(), 'sesi' => 'Pagi', 'data' => [$taruna->id => ['nadi' => '90']]])
                ->assertSessionHasNoErrors();
            $this->assertSame(2, $kegiatan->pemeriksaan()->count());
        }
    }

    public function test_aktifkan_kembali_ditolak_untuk_role_lain_dan_samapta(): void
    {
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today(), 'tanggal_selesai' => today()]);
        $samapta = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today(), 'tanggal_selesai' => today()]);

        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $this->get(route('pemeriksaan-kesehatan.show', $mptb))->assertOk()->assertDontSee('konfirmasi-aktifkan');
        $this->patch(route('pemeriksaan-kesehatan.aktifkan', $mptb))->assertForbidden();
        $this->assertTrue($mptb->refresh()->selesai());

        $this->actingAs(User::factory()->create(['role' => 'dokter']));
        $this->get(route('pemeriksaan-kesehatan.show', $samapta))->assertOk()->assertDontSee('konfirmasi-aktifkan');
        $this->patch(route('pemeriksaan-kesehatan.aktifkan', $samapta))->assertNotFound();
        $this->assertTrue($samapta->refresh()->selesai());
    }

    public function test_dokter_dan_admin_bisa_menghapus_pemeriksaan_yang_sudah_selesai_beserta_hasilnya(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();

        foreach (['dokter', 'admin'] as $role) {
            $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => "Samapta $role", 'tanggal_mulai' => today(), 'tanggal_selesai' => today()]);
            $hasil = Pemeriksaan::create(['taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $kegiatan->id, 'jenis' => 'Samapta', 'periode' => '', 'sesi' => '', 'nadi' => 80]);
            $logHasil = LogAktivitas::create(['aksi' => 'Tambah', 'sumber_daya' => 'Pemeriksaan Samapta', 'pesan' => 'Hasil ditambahkan.', 'pemeriksaan_id' => $hasil->id]);

            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertSee('Hapus pemeriksaan');
            $this->delete(route('pemeriksaan-kesehatan.destroy', $kegiatan))->assertRedirect(route('pemeriksaan-kesehatan.index'));

            $this->assertModelMissing($kegiatan);
            $this->assertModelMissing($hasil);
            $this->assertNull($logHasil->refresh()->pemeriksaan_id); // log lama tetap ada
            $this->assertSame("Pemeriksaan Samapta $role (".$kegiatan->labelRentang().') dihapus beserta 1 hasil.', LogAktivitas::latest('id')->first()->pesan);
        }
    }

    public function test_pemeriksaan_berjalan_tidak_bisa_dihapus_dan_role_lain_tidak_punya_akses_hapus(): void
    {
        $berjalan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Berjalan', 'tanggal_mulai' => today()]);
        $selesai = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Selesai', 'tanggal_mulai' => today(), 'tanggal_selesai' => today()]);

        $this->actingAs(User::factory()->create(['role' => 'dokter']));
        $this->get(route('pemeriksaan-kesehatan.show', $berjalan))->assertDontSee('Hapus pemeriksaan');
        $this->delete(route('pemeriksaan-kesehatan.destroy', $berjalan))->assertSessionHasErrors('kegiatan');
        $this->assertModelExists($berjalan);

        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $this->get(route('pemeriksaan-kesehatan.show', $selesai))->assertOk()->assertDontSee('Hapus pemeriksaan');
        $this->delete(route('pemeriksaan-kesehatan.destroy', $selesai))->assertForbidden();
        $this->assertModelExists($selesai);
    }

    public function test_mptb_menampilkan_keluhan_sesi_sebelumnya_dan_halaman_detail_taruna(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $baru = Taruna::where('tingkat', 'I')->first();
        $senior = Taruna::where('tingkat', 'II')->first();
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()->subDay()]);
        $isi = fn (string $periode, string $sesi, string $keluhan) => Pemeriksaan::create([
            'taruna_id' => $baru->id, 'kegiatan_pemeriksaan_id' => $mptb->id, 'jenis' => 'MPTB', 'periode' => $periode, 'sesi' => $sesi,
            'tekanan_darah' => '140/90', 'nadi' => 101, 'suhu' => 38, 'saturasi' => 90, 'pernapasan' => 21,
            'keluhan' => $keluhan, 'terapi' => "Terapi $keluhan",
        ]);
        $isi(today()->subDay()->toDateString(), 'Pagi', 'Pusing');
        $isi(today()->subDay()->toDateString(), 'Malam', 'Batuk');
        $isi(today()->toDateString(), 'Pagi', 'Demam');

        // Pagi <- malam kemarin; malam <- pagi hari itu
        $this->get(route('pemeriksaan-kesehatan.show', $mptb))->assertSeeInOrder(['Batuk', 'Terapi Batuk'])->assertDontSee('Terapi Pusing');
        $this->get(route('pemeriksaan-kesehatan.show', ['kegiatan' => $mptb, 'sesi' => 'Malam']))->assertSee('Terapi Demam')->assertDontSee('Terapi Batuk');
        $this->get(route('pemeriksaan-kesehatan.show', $mptb))->assertSee(route('pemeriksaan-kesehatan.detail', [$mptb, $baru]))
            ->assertSee('Lihat Informasi Lebih Lanjut')->assertSee(e('"npm":"'.$baru->npm.'"'), false); // identitas untuk popup ringkas

        $this->get(route('pemeriksaan-kesehatan.detail', [$mptb, $baru]))->assertOk()->assertSee($baru->npm)
            ->assertSeeInOrder(['Demam', 'Batuk', 'Pusing'])->assertSee('Saturasi')->assertSee('vital-danger', false); // terbaru di atas
        $this->get(route('pemeriksaan-kesehatan.detail', [$mptb, $senior]))->assertNotFound(); // MPTB hanya tingkat I
    }

    public function test_dokter_dan_admin_bisa_mengisi_pemeriksaan_dan_setiap_perubahan_tercatat_di_log(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'dokter']);
        $admin = User::factory()->create(['role' => 'admin']);
        $baru = Taruna::where('tingkat', 'I')->orderBy('nama')->first();
        $senior = Taruna::where('tingkat', 'II')->first();
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()->subDay()]);
        $tanggal = today()->toDateString();
        $kirim = fn (array $data, ?string $tgl = null) => $this->post(route('pemeriksaan-kesehatan.simpan', $mptb), ['tanggal' => $tgl ?? $tanggal, 'sesi' => 'Pagi', 'data' => $data]);

        foreach ([$perawat, $admin] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertSee('Pemeriksaan Kesehatan');
            $this->get(route('pemeriksaan-kesehatan.index'))->assertOk();
        }
        // MPTB hanya taruna tingkat I; tanggal default hari ini (di dalam rentang)
        $this->actingAs($perawat)->get(route('pemeriksaan-kesehatan.show', $mptb))->assertOk()
            ->assertSee($baru->nama)->assertDontSee($senior->nama)->assertSee('data-tanggal="'.$tanggal.'"', false)
            ->assertSee('Simpan perubahan')->assertSee('Hari ini')->assertDontSee('Perlu tindak lanjut')->assertDontSee('Keterangan');

        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36,5', 'saturasi' => '98', 'pernapasan' => '20', 'keluhan' => 'Tidak ada', 'terapi' => '', 'keterangan' => ''];
        $kirim([
            $baru->id => $vital,
            $senior->id => $vital,                                   // bukan peserta MPTB: diabaikan
            Taruna::where('tingkat', 'I')->where('id', '!=', $baru->id)->value('id') => ['nadi' => ''], // baris kosong: tidak dibuat
        ])->assertRedirect()->assertSessionHasNoErrors();

        $p = Pemeriksaan::sole();
        $this->assertSame([$baru->id, $mptb->id, 'MPTB', $tanggal, 'Pagi', '36.5', 98], [$p->taruna_id, $p->kegiatan_pemeriksaan_id, $p->jenis, $p->periode, $p->sesi, $p->suhu, $p->saturasi]);

        $kirim([$baru->id => $vital]);                               // tanpa perubahan: tidak dicatat lagi
        $kirim([$baru->id => ['suhu' => '38'] + $vital]);
        $kirim([$baru->id => ['suhu' => '99'] + $vital])->assertSessionHasErrors("data.{$baru->id}.suhu");
        $kirim([$baru->id => ['saturasi' => '101'] + $vital])->assertSessionHasErrors("data.{$baru->id}.saturasi");
        $kirim([$baru->id => $vital], today()->addDay()->toDateString())->assertSessionHasErrors('tanggal');   // belum terjadi
        $kirim([$baru->id => $vital], today()->subDays(2)->toDateString())->assertSessionHasErrors('tanggal'); // di luar rentang

        $log = LogAktivitas::orderBy('id')->get();
        $this->assertSame(['Tambah', 'Edit'], $log->pluck('aksi')->all());
        $this->assertSame([$p->id, $p->id], $log->pluck('pemeriksaan_id')->all());
        $this->assertSame('Pemeriksaan MPTB', $log[1]->sumber_daya);
        $this->assertStringContainsString("taruna {$baru->nama} (MPTB 2026 · ", $log[1]->pesan);
        $this->assertStringContainsString('suhu dari 36.5 menjadi 38', $log[1]->pesan);

        $this->get(route('pemeriksaan-kesehatan.index'))->assertDontSee('1 dari');
        $this->actingAs($admin)->get(route('log.index', ['sumber' => 'Pemeriksaan MPTB']))
            ->assertOk()->assertSee(route('log.show', $log[1]))->assertSee($baru->nama);
        $this->get(route('log.show', $log[1]))->assertOk()->assertSee('Lihat pemeriksaan')
            ->assertSee($p->url())->assertSee('suhu dari 36.5 menjadi 38');
    }

    public function test_hal_pemeriksaan_menampilkan_saturasi_dan_referensi_vital(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()]);
        $samapta = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()]);

        $this->get(route('pemeriksaan-kesehatan.show', $mptb))
            ->assertSee('Saturasi')->assertSee('95–100%')->assertSee('data-k="saturasi"', false)
            ->assertSee('Ekspor PDF')->assertSee('Ekspor Excel (CSV)');
        $this->get(route('pemeriksaan-kesehatan.show', $samapta))
            ->assertSee('Saturasi')->assertSee('95–100%')->assertSee('data-k="saturasi"', false)
            ->assertSee('Ekspor PDF')->assertSee('Ekspor Excel (CSV)');
    }

    public function test_ekspor_pemeriksaan_menyediakan_pdf_dan_excel_dengan_tanda_tangan(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $taruna = Taruna::where('tingkat', 'I')->first();
        $samapta = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()]);
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()]);

        Pemeriksaan::create([
            'taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $samapta->id, 'jenis' => 'Samapta',
            'periode' => '', 'sesi' => '', 'tekanan_darah' => '120/80', 'nadi' => 80, 'suhu' => 36.5,
            'saturasi' => 98, 'pernapasan' => 20, 'keluhan' => '=2+2', 'terapi' => 'Istirahat',
        ]);
        Pemeriksaan::create([
            'taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $mptb->id, 'jenis' => 'MPTB',
            'periode' => today()->toDateString(), 'sesi' => 'Pagi', 'tekanan_darah' => '120/80', 'nadi' => 80,
            'suhu' => 36.5, 'saturasi' => 98, 'pernapasan' => 20, 'keluhan' => 'Pagi', 'terapi' => '',
        ]);
        Pemeriksaan::create([
            'taruna_id' => $taruna->id, 'kegiatan_pemeriksaan_id' => $mptb->id, 'jenis' => 'MPTB',
            'periode' => today()->toDateString(), 'sesi' => 'Malam', 'tekanan_darah' => '120/80', 'nadi' => 80,
            'suhu' => 36.5, 'saturasi' => 90, 'pernapasan' => 20, 'keluhan' => 'Malam', 'terapi' => '',
        ]);

        $this->get(route('pemeriksaan-kesehatan.ekspor.pdf', $samapta))
            ->assertOk()->assertDownload('pemeriksaan-samapta-'.today()->toDateString().'.pdf');

        $csv = $this->get(route('pemeriksaan-kesehatan.ekspor.excel', [
            'kegiatan' => $mptb, 'tanggal' => today()->toDateString(), 'sesi' => 'Pagi',
        ]))->assertOk()->assertDownload('pemeriksaan-mptb-'.today()->toDateString().'.csv')->streamedContent();

        $this->assertStringContainsString('Saturasi (%)', $csv);
        $this->assertStringContainsString('Tanda tangan dokter penanggung jawab', $csv);
        $this->assertStringContainsString('Pagi', $csv);
        $this->assertStringNotContainsString('Malam', $csv);
        $this->assertStringContainsString("'=2+2", $this->get(route('pemeriksaan-kesehatan.ekspor.excel', $samapta))->streamedContent());
    }
}
