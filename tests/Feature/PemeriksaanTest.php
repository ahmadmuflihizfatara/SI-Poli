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
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
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

    public function test_pemeriksaan_selesai_lewat_tombol_dan_setelahnya_tidak_bisa_diisi(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today()->subDays(3)]);
        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36.5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada'];

        $this->actingAs(User::factory()->create(['role' => 'perawat', 'akses_edit' => false]))
            ->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->patch(route('pemeriksaan-kesehatan.selesai', $kegiatan))->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan));
        $this->assertSame(today()->toDateString(), $kegiatan->refresh()->tanggal_selesai->toDateString());
        $this->assertStringContainsString('dinyatakan selesai', LogAktivitas::sole()->pesan);

        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['data' => [$taruna->id => $vital]])->assertSessionHasErrors('kegiatan');
        $this->assertSame(0, Pemeriksaan::count());
        $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('sudah selesai')->assertDontSee('Selesaikan pemeriksaan');
        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('Selesai');
    }

    public function test_mptb_menampilkan_keluhan_sesi_sebelumnya_dan_halaman_detail_taruna(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $baru = Taruna::where('tingkat', 'I')->first();
        $senior = Taruna::where('tingkat', 'II')->first();
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()->subDay()]);
        $isi = fn (string $periode, string $sesi, string $keluhan) => Pemeriksaan::create([
            'taruna_id' => $baru->id, 'kegiatan_pemeriksaan_id' => $mptb->id, 'jenis' => 'MPTB', 'periode' => $periode, 'sesi' => $sesi, 'keluhan' => $keluhan, 'terapi' => "Terapi $keluhan",
        ]);
        $isi(today()->subDay()->toDateString(), 'Pagi', 'Pusing');
        $isi(today()->subDay()->toDateString(), 'Malam', 'Batuk');
        $isi(today()->toDateString(), 'Pagi', 'Demam');

        // Pagi <- malam kemarin; malam <- pagi hari itu
        $this->get(route('pemeriksaan-kesehatan.show', $mptb))->assertSeeInOrder(['Batuk', 'Terapi Batuk'])->assertDontSee('Terapi Pusing');
        $this->get(route('pemeriksaan-kesehatan.show', ['kegiatan' => $mptb, 'sesi' => 'Malam']))->assertSee('Terapi Demam')->assertDontSee('Terapi Batuk');
        $this->get(route('pemeriksaan-kesehatan.show', $mptb))->assertSee(route('pemeriksaan-kesehatan.detail', [$mptb, $baru]));

        $this->get(route('pemeriksaan-kesehatan.detail', [$mptb, $baru]))->assertOk()->assertSee($baru->npm)
            ->assertSeeInOrder(['Demam', 'Batuk', 'Pusing']); // terbaru di atas
        $this->get(route('pemeriksaan-kesehatan.detail', [$mptb, $senior]))->assertNotFound(); // MPTB hanya tingkat I
    }

    public function test_perawat_dan_admin_bisa_mengisi_pemeriksaan_dan_setiap_perubahan_tercatat_di_log(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'perawat']);
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

        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36,5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada', 'terapi' => '', 'keterangan' => ''];
        $kirim([
            $baru->id => $vital,
            $senior->id => $vital,                                   // bukan peserta MPTB: diabaikan
            Taruna::where('tingkat', 'I')->where('id', '!=', $baru->id)->value('id') => ['nadi' => ''], // baris kosong: tidak dibuat
        ])->assertRedirect()->assertSessionHasNoErrors();

        $p = Pemeriksaan::sole();
        $this->assertSame([$baru->id, $mptb->id, 'MPTB', $tanggal, 'Pagi', '36.5'], [$p->taruna_id, $p->kegiatan_pemeriksaan_id, $p->jenis, $p->periode, $p->sesi, $p->suhu]);

        $kirim([$baru->id => $vital]);                               // tanpa perubahan: tidak dicatat lagi
        $kirim([$baru->id => ['suhu' => '38'] + $vital]);
        $kirim([$baru->id => ['suhu' => '99'] + $vital])->assertSessionHasErrors("data.{$baru->id}.suhu");
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
}
