<?php

namespace Tests\Feature;

use App\Models\Fenomena;
use App\Models\Indikator;
use App\Models\JenisFenomena;
use App\Models\SektorUsaha;
use App\Models\SumberBerita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FenomenaTest extends TestCase
{
    use RefreshDatabase;

    private function createProvinceUser()
    {
        $kabupaten = \App\Models\Kabupaten::create([
            'kode_kab' => '6100',
            'nama_kabupaten' => 'Provinsi'
        ]);

        return User::factory()->create([
            'kabupaten_id' => $kabupaten->id,
            'status' => 'active',
            'role' => 'member'
        ]);
    }

    public function test_can_access_input_page()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->get(route('fenomena.create'));

        $response->assertStatus(200);
        $response->assertViewHasAll(['sektorUsahas', 'indikators', 'jenisFenomenas', 'sumberBeritas']);
    }

    public function test_can_store_fenomena()
    {
        $user = $this->createProvinceUser();
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $data = [
            'link_berita' => 'https://example.com',
            'tanggal' => 26,
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul Test',
            'penjelasan' => 'Penjelasan Test',
            'sektor_usaha_id' => $sektor->id,
            'indikator_id' => $indikator->id,
            'jenis_fenomena_ids' => [$jenis->id],
            'sumber_berita_id' => $sumber->id,
        ];

        $response = $this->actingAs($user)->post(route('fenomena.store'), $data);

        $response->assertRedirect(route('fenomena.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fenomenas', [
            'judul' => 'Judul Test',
            'penjelasan' => 'Penjelasan Test',
            'sumber_berita_id' => $sumber->id,
            'status_verifikasi' => 'P',
        ]);

        $fenomena = Fenomena::first();
        $this->assertEquals(1, $fenomena->sektors()->count());
        $this->assertEquals(1, $fenomena->indikators()->count());
        $this->assertNull($fenomena->indikators()->first()->pivot->arah);
        $this->assertEquals(1, $fenomena->jenisFenomenas()->count());
    }

    public function test_store_validation()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->post(route('fenomena.store'), []);

        $response->assertSessionHasErrors([
            'tanggal' => 'Tanggal wajib diisi.',
            'bulan' => 'Bulan wajib dipilih.',
            'tahun' => 'Tahun wajib diisi.',
            'judul' => 'Judul fenomena wajib diisi.',
            'penjelasan' => 'Penjelasan fenomena wajib diisi.',
            'sektor_usaha_id' => 'Kode Lapangan Usaha wajib dipilih.',
            'indikator_id' => 'Kode Indikator wajib dipilih.',
            'jenis_fenomena_ids' => 'Jenis fenomena wajib dipilih minimal satu.',
            'sumber_berita_id' => 'Sumber berita wajib dipilih.',
        ]);
    }

    public function test_link_berita_required_for_online_source()
    {
        $user = $this->createProvinceUser();
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $onlineSource = SumberBerita::create(['nama' => 'Sumber Online', 'is_online' => true]);

        $data = [
            'tanggal' => 26,
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul Test',
            'penjelasan' => 'Penjelasan Test',
            'sektor_usaha_id' => $sektor->id,
            'indikator_id' => $indikator->id,
            'arah' => 'naik',
            'jenis_fenomena_ids' => [$jenis->id],
            'sumber_berita_id' => $onlineSource->id,
            // 'link_berita' is missing
        ];

        $response = $this->actingAs($user)->post(route('fenomena.store'), $data);

        $response->assertSessionHasErrors(['link_berita' => 'Link berita wajib diisi untuk sumber berita online.']);
    }

    public function test_link_berita_optional_for_offline_source()
    {
        $user = $this->createProvinceUser();
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $offlineSource = SumberBerita::create(['nama' => 'Sumber Offline', 'is_online' => false]);

        $data = [
            'tanggal' => 26,
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul Test',
            'penjelasan' => 'Penjelasan Test',
            'sektor_usaha_id' => $sektor->id,
            'indikator_id' => $indikator->id,
            'arah' => 'naik',
            'jenis_fenomena_ids' => [$jenis->id],
            'sumber_berita_id' => $offlineSource->id,
            // 'link_berita' is missing but should be fine
        ];

        $response = $this->actingAs($user)->post(route('fenomena.store'), $data);

        $response->assertRedirect(route('fenomena.index'));
        $response->assertSessionHas('success');
    }
    public function test_duplicate_judul_is_rejected()
    {
        $user = $this->createProvinceUser();
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        // Create first record
        Fenomena::create([
            'tanggal_berita' => '2026-02-26',
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul Unik',
            'penjelasan' => 'Penjelasan',
            'sumber_berita_id' => $sumber->id,
            'status_verifikasi' => 'P',
            'created_by' => $user->id,
        ]);

        $data = [
            'tanggal' => 26,
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul Unik', // Duplicate
            'penjelasan' => 'Penjelasan Lain',
            'sektor_usaha_id' => $sektor->id,
            'indikator_id' => $indikator->id,
            'arah' => 'naik',
            'jenis_fenomena_ids' => [$jenis->id],
            'sumber_berita_id' => $sumber->id,
        ];

        $response = $this->actingAs($user)->post(route('fenomena.store'), $data);

        $response->assertSessionHasErrors(['judul' => 'Judul fenomena sudah pernah dimasukkan.']);
    }

    public function test_duplicate_link_berita_is_rejected()
    {
        $user = $this->createProvinceUser();
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        // Create first record
        Fenomena::create([
            'tanggal_berita' => '2026-02-26',
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul 1',
            'penjelasan' => 'Penjelasan',
            'link_berita' => 'https://example.com/berita-1',
            'sumber_berita_id' => $sumber->id,
            'status_verifikasi' => 'P',
            'created_by' => $user->id,
        ]);

        $data = [
            'tanggal' => 26,
            'bulan' => 2,
            'tahun' => 2026,
            'judul' => 'Judul 2',
            'penjelasan' => 'Penjelasan',
            'link_berita' => 'https://example.com/berita-1', // Duplicate
            'sektor_usaha_id' => $sektor->id,
            'indikator_id' => $indikator->id,
            'arah' => 'naik',
            'jenis_fenomena_ids' => [$jenis->id],
            'sumber_berita_id' => $sumber->id,
        ];

        $response = $this->actingAs($user)->post(route('fenomena.store'), $data);

        $response->assertSessionHasErrors(['link_berita' => 'Link berita sudah pernah digunakan.']);
    }
}
