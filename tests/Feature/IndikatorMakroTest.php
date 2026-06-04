<?php

namespace Tests\Feature;

use App\Models\PeriodeIndikator;
use App\Models\IndikatorBidang;
use App\Models\IndikatorMakro;
use App\Models\Dimensi;
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
            'created_by' => $user->id,
            'is_active' => true
        ]);
    }

    public function test_cannot_store_bidang_with_duplicate_slug()
    {
        $user = $this->createProvinceUser();

        IndikatorBidang::create([
            'nama_bidang' => 'Sosial Budaya',
            'is_active' => true,
            'created_by' => $user->id
        ]);

        // Attempting with exact same name which generates the same slug
        $response = $this->actingAs($user)->post(route('indikator-makro.bidang.store'), [
            'nama_bidang' => 'Sosial Budaya',
            'is_active' => '1'
        ]);

        $response->assertSessionHasErrors(['nama_bidang']);
    }

    public function test_can_toggle_bidang_active_status()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create([
            'nama_bidang' => 'Sosial Budaya',
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
        $b1 = IndikatorBidang::create(['nama_bidang' => 'B1', 'urutan' => 1]);
        $b2 = IndikatorBidang::create(['nama_bidang' => 'B2', 'urutan' => 2]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.bidang.reorder'), [
            'order' => [$b2->id, $b1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, $b2->refresh()->urutan);
        $this->assertEquals(2, $b1->refresh()->urutan);
    }

    public function test_can_access_input_nilai_page()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->get(route('indikator-makro.input-nilai'));

        $response->assertStatus(200);
        $response->assertViewIs('indikator-makro.input-nilai');
        $response->assertViewHas('periodeIndikators');
        $response->assertViewHas('kabupatens');
    }

    public function test_can_store_and_update_nilai_indikator_makros()
    {
        $user = $this->createProvinceUser();

        $periode = PeriodeIndikator::create(['tahun' => 2026, 'is_active' => true]);
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);
        $dimensi = Dimensi::create(['nama_dimensi' => 'Kesehatan']);
        $indDim = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimensi->id,
            'is_active' => true
        ]);

        // Post new value
        $response = $this->actingAs($user)->post(route('indikator-makro.store-nilai'), [
            'periode_indikator_id' => $periode->id,
            'indikator_makro_id' => $makro->id,
            'nilai' => [
                $user->kabupaten_id => [
                    $indDim->id => '82.5'
                ]
            ]
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('nilai_indikator_makros', [
            'periode_indikator_id' => $periode->id,
            'kabupaten_id' => $user->kabupaten_id,
            'indikator_dimensi_id' => $indDim->id,
            'nilai' => 82.5
        ]);

        // Post empty value (should delete the record)
        $response = $this->actingAs($user)->post(route('indikator-makro.store-nilai'), [
            'periode_indikator_id' => $periode->id,
            'indikator_makro_id' => $makro->id,
            'nilai' => [
                $user->kabupaten_id => [
                    $indDim->id => ''
                ]
            ]
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('nilai_indikator_makros', [
            'periode_indikator_id' => $periode->id,
            'kabupaten_id' => $user->kabupaten_id,
            'indikator_dimensi_id' => $indDim->id
        ]);
    }

    public function test_kelola_indikator_dimensi_filtered_by_makro()
    {
        $user = $this->createProvinceUser();

        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);
        $dimensi = Dimensi::create(['nama_dimensi' => 'Kesehatan']);
        $indDim = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimensi->id,
            'is_active' => true
        ]);

        // Access without filter
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', ['tab' => 'indikator-dimensi']));
        $response->assertStatus(200);
        $response->assertSee('Silakan pilih Indikator Makro terlebih dahulu');
        $response->assertDontSee('id="sortable-ind-dimensi"', false);

        // Access with filter
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'indikator-dimensi',
            'filter_makro_id' => $makro->id
        ]));
        $response->assertStatus(200);
        $response->assertDontSee('Silakan pilih Indikator Makro terlebih dahulu');
        $response->assertSee('id="sortable-ind-dimensi"', false);
    }

    public function test_can_toggle_makro_active_status()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create([
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.makro.toggle', $makro->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false
        ]);

        $this->assertFalse((bool)$makro->refresh()->is_active);
    }

    public function test_none_dimension_validation_rules()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);
        
        $dimNone = Dimensi::create(['nama_dimensi' => 'None']);
        $dimNormal = Dimensi::create(['nama_dimensi' => 'Persentase']);

        // Case 1: Macro indicator A has no relations, should be allowed to store "none"
        $response = $this->actingAs($user)->post(route('indikator-makro.indikator-dimensi.store'), [
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimNone->id
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('indikator_dimensi', [
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimNone->id
        ]);

        // Case 2: Macro indicator A already has "none", trying to store "Persentase" should fail
        $response = $this->actingAs($user)->post(route('indikator-makro.indikator-dimensi.store'), [
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimNormal->id
        ]);
        $response->assertSessionHasErrors(['indikator_dimensi']);

        // Clear IPM relations
        \App\Models\IndikatorDimensi::where('indikator_makro_id', $makro->id)->delete();

        // Case 3: Macro indicator A has "Persentase", trying to store "none" should fail
        \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimNormal->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($user)->post(route('indikator-makro.indikator-dimensi.store'), [
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimNone->id
        ]);
        $response->assertSessionHasErrors(['indikator_dimensi']);
    }

    public function test_back_button_behavior_with_from_input_nilai_parameter()
    {
        $user = $this->createProvinceUser();

        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'from' => 'input-nilai',
            'periode_indikator_id' => 12,
            'indikator_makro_id' => 34
        ]));

        $response->assertStatus(200);
        $response->assertSee(route('indikator-makro.input-nilai', [
            'periode_indikator_id' => 12,
            'indikator_makro_id' => 34
        ]));
    }
}
