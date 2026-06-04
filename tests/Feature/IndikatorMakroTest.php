<?php

namespace Tests\Feature;

use App\Models\PeriodeIndikator;
use App\Models\IndikatorBidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndikatorMakroTest extends TestCase
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

    public function test_can_access_kelola_page()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->get(route('indikator-makro.kelola'));

        $response->assertStatus(200);
        $response->assertViewIs('indikator-makro.kelola');
    }

    public function test_cannot_store_duplicate_periode_tahun()
    {
        $user = $this->createProvinceUser();

        PeriodeIndikator::create([
            'tahun' => 2026,
            'is_active' => true
        ]);

        $response = $this->actingAs($user)->post(route('indikator-makro.periode.store'), [
            'tahun' => 2026,
            'is_active' => '1'
        ]);

        $response->assertSessionHasErrors(['tahun']);
    }

    public function test_cannot_update_periode_to_duplicate_tahun()
    {
        $user = $this->createProvinceUser();

        $p1 = PeriodeIndikator::create(['tahun' => 2025, 'is_active' => true]);
        $p2 = PeriodeIndikator::create(['tahun' => 2026, 'is_active' => true]);

        $response = $this->actingAs($user)->put(route('indikator-makro.periode.update', $p2->id), [
            'tahun' => 2025,
            'is_active' => '1'
        ]);

        $response->assertSessionHasErrors(['tahun']);
    }

    public function test_can_toggle_periode_active_status()
    {
        $user = $this->createProvinceUser();
        $periode = PeriodeIndikator::create(['tahun' => 2026, 'is_active' => true]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.periode.toggle', $periode->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        
        $this->assertFalse($periode->refresh()->is_active);
    }

    public function test_can_store_bidang_with_auto_slug_and_user_tracking()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->post(route('indikator-makro.bidang.store'), [
            'nama_bidang' => 'Sosial Budaya',
            'is_active' => '1'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('indikator_bidangs', [
            'nama_bidang' => 'Sosial Budaya',
            'slug' => 'sosial-budaya',
            'created_by' => $user->id,
            'is_active' => true
        ]);
    }

    public function test_cannot_store_bidang_with_duplicate_slug()
    {
        $user = $this->createProvinceUser();

        IndikatorBidang::create([
            'nama_bidang' => 'Sosial Budaya',
            'slug' => 'sosial-budaya',
            'is_active' => true,
            'created_by' => $user->id
        ]);

        // Attempting with exact same name which generates the same slug
        $response = $this->actingAs($user)->post(route('indikator-makro.bidang.store'), [
            'nama_bidang' => 'Sosial Budaya',
            'is_active' => '1'
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    public function test_can_toggle_bidang_active_status()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create([
            'nama_bidang' => 'Sosial Budaya',
            'slug' => 'sosial-budaya',
            'is_active' => true,
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.bidang.toggle', $bidang->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        
        $this->assertFalse($bidang->refresh()->is_active);
        $this->assertEquals($user->id, $bidang->updated_by);
    }

    public function test_can_reorder_bidangs()
    {
        $user = $this->createProvinceUser();
        $b1 = IndikatorBidang::create(['nama_bidang' => 'B1', 'slug' => 'b1', 'urutan' => 1]);
        $b2 = IndikatorBidang::create(['nama_bidang' => 'B2', 'slug' => 'b2', 'urutan' => 2]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.bidang.reorder'), [
            'order' => [$b2->id, $b1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, $b2->refresh()->urutan);
        $this->assertEquals(2, $b1->refresh()->urutan);
    }
}
