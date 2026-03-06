<?php

namespace Tests\Feature;

use App\Models\Fenomena;
use App\Models\Indikator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FenomenaVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser()
    {
        $kabupaten = \App\Models\Kabupaten::create([
            'kode_kab' => '6100',
            'nama_kabupaten' => 'Provinsi'
        ]);

        return User::factory()->create([
            'kabupaten_id' => $kabupaten->id,
            'status' => 'active',
            'role' => 'admin' // Assuming admin has verification rights
        ]);
    }

    public function test_can_access_verification_index()
    {
        $user = $this->createAdminUser();
        $response = $this->actingAs($user)->get(route('fenomena.verification.index'));

        $response->assertStatus(200);
        $response->assertViewIs('fenomena.verification.index');
        $response->assertViewHas('fenomenas');
    }

    public function test_can_access_verification_form()
    {
        $user = $this->createAdminUser();

        $fenomena = Fenomena::factory()->create(['status_verifikasi' => 'P']);

        $response = $this->actingAs($user)->get(route('fenomena.verification.show', $fenomena->id));

        $response->assertStatus(200);
        $response->assertViewIs('fenomena.verification.form');
        $response->assertViewHasAll(['fenomena', 'impactIndikators']);
    }

    public function test_can_approve_fenomena_with_directions()
    {
        $user = $this->createAdminUser();

        // Setup Fenomena with primary indicator
        $fenomena = Fenomena::factory()->create(['status_verifikasi' => 'P']);
        $utama = Indikator::create(['kode' => '01', 'nama' => 'Utama 1', 'kelompok' => 'utama']);
        $dampak1 = Indikator::create(['kode' => '02', 'nama' => 'Dampak 1', 'kelompok' => 'dampak']);
        $dampak2 = Indikator::create(['kode' => '03', 'nama' => 'Dampak 2', 'kelompok' => 'dampak']);

        $fenomena->indikators()->attach($utama->id, ['arah' => null]);

        $data = [
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

        // Check Utama direction
        $this->assertEquals('naik', $fenomena->indikators()->where('kelompok', 'utama')->first()->pivot->arah);

        // Check Impacts
        $this->assertEquals(3, $fenomena->indikators()->count());
        $this->assertEquals('turun', $fenomena->indikators()->find($dampak1->id)->pivot->arah);
        $this->assertEquals('tetap', $fenomena->indikators()->find($dampak2->id)->pivot->arah);
    }

    public function test_can_reject_fenomena()
    {
        $user = $this->createAdminUser();
        $fenomena = Fenomena::factory()->create(['status_verifikasi' => 'P']);

        $data = [
            'status_verifikasi' => 'T',
        ];

        $response = $this->actingAs($user)->post(route('fenomena.verification.store', $fenomena->id), $data);

        $response->assertRedirect(route('fenomena.verification.index'));

        $fenomena->refresh();
        $this->assertEquals('T', $fenomena->status_verifikasi);
    }
}
