<?php

namespace Tests\Feature;

use App\Models\Fenomena;
use App\Models\Indikator;
use App\Models\JenisFenomena;
use App\Models\SektorUsaha;
use App\Models\SumberBerita;
use App\Models\User;
use App\Models\Kabupaten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $kabupaten = Kabupaten::create([
            'kode_kab' => '6100',
            'nama_kabupaten' => 'Provinsi'
        ]);

        $this->user = User::factory()->create([
            'kabupaten_id' => $kabupaten->id,
            'status' => 'active',
            'role' => 'member'
        ]);
    }

    public function test_cannot_delete_sektor_usaha_in_use()
    {
        $sektor = SektorUsaha::create(['kode' => 'A', 'nama' => 'Sektor A']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'created_by' => $this->user->id,
            'sumber_berita_id' => $sumber->id
        ]);
        $fenomena->sektors()->attach($sektor->id);

        $response = $this->actingAs($this->user)
            ->from(route('fenomena.kelola'))
            ->delete(route('sektor-usaha.destroy', $sektor->id));

        $response->assertRedirect(route('fenomena.kelola'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus Kode Lapangan Usaha ini karena sudah digunakan dalam data fenomena.');
        $this->assertDatabaseHas('sektor_usahas', ['id' => $sektor->id]);
    }

    public function test_can_delete_sektor_usaha_not_in_use()
    {
        $sektor = SektorUsaha::create(['kode' => 'B', 'nama' => 'Sektor B']);

        $response = $this->actingAs($this->user)
            ->from(route('fenomena.kelola'))
            ->delete(route('sektor-usaha.destroy', $sektor->id));

        $response->assertRedirect(route('fenomena.kelola'));
        $response->assertSessionHas('success', 'Data Kode Lapangan Usaha berhasil dihapus.');
        $this->assertDatabaseMissing('sektor_usahas', ['id' => $sektor->id]);
    }

    public function test_cannot_delete_indikator_in_use()
    {
        $indikator = Indikator::create(['kode' => '01', 'nama' => 'Indikator 1', 'kelompok' => 'utama']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'created_by' => $this->user->id,
            'sumber_berita_id' => $sumber->id
        ]);
        $fenomena->indikators()->attach($indikator->id, ['arah' => 'naik']);

        $response = $this->actingAs($this->user)
            ->from(route('fenomena.kelola'))
            ->delete(route('indikator.destroy', $indikator->id));

        $response->assertRedirect(route('fenomena.kelola'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus Kode Indikator ini karena sudah digunakan dalam data fenomena.');
        $this->assertDatabaseHas('indikators', ['id' => $indikator->id]);
    }

    public function test_cannot_delete_jenis_fenomena_in_use()
    {
        $jenis = JenisFenomena::create(['nama' => 'Jenis 1']);
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);

        $fenomena = Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'created_by' => $this->user->id,
            'sumber_berita_id' => $sumber->id
        ]);
        $fenomena->jenisFenomenas()->attach($jenis->id);

        $response = $this->actingAs($this->user)
            ->from(route('fenomena.kelola'))
            ->delete(route('jenis-fenomena.destroy', $jenis->id));

        $response->assertRedirect(route('fenomena.kelola'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus Jenis Fenomena ini karena sudah digunakan dalam data fenomena.');
        $this->assertDatabaseHas('jenis_fenomenas', ['id' => $jenis->id]);
    }

    public function test_cannot_delete_sumber_berita_in_use()
    {
        $sumber = SumberBerita::create(['nama' => 'Sumber 1']);
        Fenomena::factory()->create([
            'status_verifikasi' => 'P',
            'created_by' => $this->user->id,
            'sumber_berita_id' => $sumber->id
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('fenomena.kelola'))
            ->delete(route('sumber-berita.destroy', $sumber->id));

        $response->assertRedirect(route('fenomena.kelola'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus Sumber Berita ini karena sudah digunakan dalam data fenomena.');
        $this->assertDatabaseHas('sumber_beritas', ['id' => $sumber->id]);
    }
}
