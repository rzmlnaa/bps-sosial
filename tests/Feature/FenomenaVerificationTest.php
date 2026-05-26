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

class FenomenaVerificationTest extends TestCase
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

    public function test_can_access_verification_index()
    {
        $user = $this->createProvinceUser();
        $response = $this->actingAs($user)->get(route('fenomena.verification.index'));

        $response->assertStatus(200);
        $response->assertViewIs('fenomena.verification.index');
        $response->assertViewHas('fenomenas');
    }

    public function test_can_access_verification_form()
    {
        $user = $this->createProvinceUser();

        $fenomena = Fenomena::factory()->create(['status_verifikasi' => 'P']);

        $response = $this->actingAs($user)->get(route('fenomena.verification.show', $fenomena->id));

        $response->assertStatus(200);
        $response->assertViewIs('fenomena.verification.form');
        $response->assertViewHasAll(['fenomena', 'impactIndikators']);
    }

    public function test_can_approve_fenomena_with_directions()
    {
        $user = $this->createProvinceUser();

        // Setup Sektor, Jenis, Sumber, Indikator
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        // Setup Fenomena with primary indicator
        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'sumber_berita_id' => $sumber->id,
        ]);
        $utama = Indikator::create(['kode' => '01', 'nama' => 'Utama 1', 'kelompok' => 'utama', 'is_active' => 1]);
        $dampak1 = Indikator::create(['kode' => '02', 'nama' => 'Dampak 1', 'kelompok' => 'dampak', 'is_active' => 1]);
        $dampak2 = Indikator::create(['kode' => '03', 'nama' => 'Dampak 2', 'kelompok' => 'dampak', 'is_active' => 1]);

        $fenomena->sektors()->attach($sektor->id);
        $fenomena->jenisFenomenas()->attach($jenis->id);
        $fenomena->indikators()->attach($utama->id, ['arah' => null]);

        $data = [
            'judul' => 'Judul Baru',
            'tanggal_berita' => '2026-05-26',
            'sumber_berita_id' => $sumber->id,
            'penjelasan' => 'Isi penjelasan baru',
            'sektor_usaha_id' => $sektor->id,
            'jenis_fenomena_ids' => [$jenis->id],
            'indikator_id' => $utama->id,
            'status_verifikasi' => 'Y',
            'arah_utama' => 'naik',
            'impact_directions' => [
                $dampak1->id => 'turun',
                $dampak2->id => 'tetap',
            ],
        ];

        $response = $this->actingAs($user)->post(route('fenomena.verification.store', $fenomena->id), $data);

        $response->assertRedirect(route('fenomena.verification.index'));
        $response->assertSessionHas('success');

        $fenomena->refresh();
        $this->assertEquals('Y', $fenomena->status_verifikasi);
        $this->assertEquals($user->id, $fenomena->verified_by);
        $this->assertEquals('Judul Baru', $fenomena->judul);
        $this->assertEquals('Isi penjelasan baru', $fenomena->penjelasan);

        // Check Utama direction
        $this->assertEquals('naik', $fenomena->indikators()->where('kelompok', 'utama')->first()->pivot->arah);

        // Check Impacts
        $this->assertEquals(3, $fenomena->indikators()->count());
        $this->assertEquals('turun', $fenomena->indikators()->find($dampak1->id)->pivot->arah);
        $this->assertEquals('tetap', $fenomena->indikators()->find($dampak2->id)->pivot->arah);
    }

    public function test_can_reject_fenomena()
    {
        $user = $this->createProvinceUser();

        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'sumber_berita_id' => $sumber->id,
        ]);
        $utama = Indikator::create(['kode' => '01', 'nama' => 'Utama 1', 'kelompok' => 'utama', 'is_active' => 1]);
        $fenomena->sektors()->attach($sektor->id);
        $fenomena->jenisFenomenas()->attach($jenis->id);
        $fenomena->indikators()->attach($utama->id, ['arah' => null]);

        $data = [
            'judul' => $fenomena->judul,
            'tanggal_berita' => $fenomena->tanggal_berita,
            'sumber_berita_id' => $sumber->id,
            'penjelasan' => $fenomena->penjelasan,
            'sektor_usaha_id' => $sektor->id,
            'jenis_fenomena_ids' => [$jenis->id],
            'indikator_id' => $utama->id,
            'status_verifikasi' => 'T',
        ];

        $response = $this->actingAs($user)->post(route('fenomena.verification.store', $fenomena->id), $data);

        $response->assertRedirect(route('fenomena.verification.index'));

        $fenomena->refresh();
        $this->assertEquals('T', $fenomena->status_verifikasi);
    }

    public function test_can_access_edit_verification_form()
    {
        $user = $this->createProvinceUser();

        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'Y',
            'sumber_berita_id' => $sumber->id,
        ]);
        $utama = Indikator::create(['kode' => '01', 'nama' => 'Utama 1', 'kelompok' => 'utama', 'is_active' => 1]);
        $fenomena->sektors()->attach($sektor->id);
        $fenomena->jenisFenomenas()->attach($jenis->id);
        $fenomena->indikators()->attach($utama->id, ['arah' => 'naik']);

        $response = $this->actingAs($user)->get(route('fenomena.verification.edit', $fenomena->id));

        $response->assertStatus(200);
        $response->assertViewIs('fenomena.verification.edit');
        $response->assertViewHasAll(['fenomena', 'sektorUsahas', 'utamaIndikators', 'jenisFenomenas', 'sumberBeritas', 'impactIndikators']);
    }

    public function test_can_update_verification_and_phenomenon_details()
    {
        $user = $this->createProvinceUser();

        // Setup database entities
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $sektorBaru = SektorUsaha::create(['kode' => 'B', 'nama' => 'Sektor B']);
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $jenisBaru = JenisFenomena::create(['nama' => 'Jenis Baru']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);
        $sumberBaru = SumberBerita::create(['nama' => 'Sumber Baru']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'Y',
            'sumber_berita_id' => $sumber->id,
        ]);

        $utama = Indikator::create(['kode' => '01', 'nama' => 'Utama 1', 'kelompok' => 'utama', 'is_active' => 1]);
        $utamaBaru = Indikator::create(['kode' => '02', 'nama' => 'Utama Baru', 'kelompok' => 'utama', 'is_active' => 1]);
        $dampak = Indikator::create(['kode' => '03', 'nama' => 'Dampak', 'kelompok' => 'dampak', 'is_active' => 1]);

        $fenomena->sektors()->attach($sektor->id);
        $fenomena->jenisFenomenas()->attach($jenis->id);
        $fenomena->indikators()->attach($utama->id, ['arah' => 'naik']);

        $data = [
            'judul' => 'Judul Terupdate',
            'tanggal_berita' => '2026-05-27',
            'sumber_berita_id' => $sumberBaru->id,
            'penjelasan' => 'Penjelasan terupdate',
            'sektor_usaha_id' => $sektorBaru->id,
            'jenis_fenomena_ids' => [$jenisBaru->id],
            'indikator_id' => $utamaBaru->id,
            'status_verifikasi' => 'Y',
            'arah_utama' => 'turun',
            'impact_directions' => [
                $dampak->id => 'naik'
            ]
        ];

        $response = $this->actingAs($user)->put(route('fenomena.verification.update', $fenomena->id), $data);

        $response->assertRedirect(route('fenomena.verification.index', ['tab' => 'riwayat']));
        $response->assertSessionHas('success');

        $fenomena->refresh();
        $this->assertEquals('Y', $fenomena->status_verifikasi);
        $this->assertEquals('Judul Terupdate', $fenomena->judul);
        $this->assertEquals('2026-05-27', $fenomena->tanggal_berita);
        $this->assertEquals($sumberBaru->id, $fenomena->sumber_berita_id);
        $this->assertEquals('Penjelasan terupdate', $fenomena->penjelasan);

        // Check synced Sektor
        $this->assertEquals($sektorBaru->id, $fenomena->sektors->first()->id);

        // Check synced Jenis
        $this->assertEquals($jenisBaru->id, $fenomena->jenisFenomenas->first()->id);

        // Check Utama direction updated
        $this->assertEquals('turun', $fenomena->indikators()->where('kelompok', 'utama')->first()->pivot->arah);
        $this->assertEquals($utamaBaru->id, $fenomena->indikators()->where('kelompok', 'utama')->first()->id);

        // Check Impact direction updated
        $this->assertEquals('naik', $fenomena->indikators()->find($dampak->id)->pivot->arah);
    }
}
