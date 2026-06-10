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
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('indikator_bidangs', [
            'nama_bidang' => 'Sosial Budaya',
            'created_by' => $user->id,
        ]);
    }

    public function test_cannot_store_bidang_with_duplicate_slug()
    {
        $user = $this->createProvinceUser();

        IndikatorBidang::create([
            'nama_bidang' => 'Sosial Budaya',
            'created_by' => $user->id
        ]);

        // Attempting with exact same name which generates the same slug
        $response = $this->actingAs($user)->post(route('indikator-makro.bidang.store'), [
            'nama_bidang' => 'Sosial Budaya',
        ]);

        $response->assertSessionHasErrors(['nama_bidang']);
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
        $this->assertEquals($user->id, $b2->refresh()->updated_by);
        $this->assertEquals($user->id, $b1->refresh()->updated_by);
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

    public function test_kelola_indikator_makro_filtered_by_bidang()
    {
        $user = $this->createProvinceUser();

        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);

        // Access without filter
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', ['tab' => 'makro']));
        $response->assertStatus(200);
        $response->assertSee('Silakan pilih Bidang terlebih dahulu');
        $response->assertDontSee('id="sortable-makro"', false);

        // Access with filter
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'makro',
            'filter_bidang_id' => $bidang->id
        ]));
        $response->assertStatus(200);
        $response->assertDontSee('Silakan pilih Bidang terlebih dahulu');
        $response->assertSee('id="sortable-makro"', false);
    }

    public function test_cannot_store_duplicate_makro_in_same_bidang()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        IndikatorMakro::create([
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang->id
        ]);

        $response = $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang->id,
            'nama_indikator' => 'IPM',
        ]);

        $response->assertSessionHasErrors(['nama_indikator']);
    }

    public function test_can_store_duplicate_makro_in_different_bidang()
    {
        $user = $this->createProvinceUser();
        $bidang1 = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $bidang2 = IndikatorBidang::create(['nama_bidang' => 'Ekonomi']);
        IndikatorMakro::create([
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang1->id
        ]);

        $response = $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang2->id,
            'nama_indikator' => 'IPM',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('indikator_makros', [
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang2->id
        ]);
    }

    public function test_cannot_update_makro_to_duplicate_in_same_bidang()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro1 = IndikatorMakro::create([
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang->id,
            'urutan' => 1
        ]);
        $makro2 = IndikatorMakro::create([
            'nama_indikator' => 'PDRB',
            'indikator_bidang_id' => $bidang->id,
            'urutan' => 2
        ]);

        $response = $this->actingAs($user)->put(route('indikator-makro.makro.update', $makro2->id), [
            'indikator_bidang_id' => $bidang->id,
            'nama_indikator' => 'IPM',
            'urutan' => 2
        ]);

        $response->assertSessionHasErrors(['nama_indikator']);
    }

    public function test_can_update_makro_to_duplicate_in_different_bidang()
    {
        $user = $this->createProvinceUser();
        $bidang1 = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $bidang2 = IndikatorBidang::create(['nama_bidang' => 'Ekonomi']);
        $makro1 = IndikatorMakro::create([
            'nama_indikator' => 'IPM',
            'indikator_bidang_id' => $bidang1->id,
            'urutan' => 1
        ]);
        $makro2 = IndikatorMakro::create([
            'nama_indikator' => 'PDRB',
            'indikator_bidang_id' => $bidang2->id,
            'urutan' => 1
        ]);

        $response = $this->actingAs($user)->put(route('indikator-makro.makro.update', $makro2->id), [
            'indikator_bidang_id' => $bidang2->id,
            'nama_indikator' => 'IPM',
            'urutan' => 1
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('IPM', $makro2->refresh()->nama_indikator);
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

    public function test_can_search_makro_indicators_via_ajax()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        
        $makro1 = IndikatorMakro::create([
            'nama_indikator' => 'Indeks Pembangunan Manusia',
            'indikator_bidang_id' => $bidang->id,
            'is_active' => true
        ]);
        $makro2 = IndikatorMakro::create([
            'nama_indikator' => 'Tingkat Pengangguran Terbuka',
            'indikator_bidang_id' => $bidang->id,
            'is_active' => true
        ]);
        $makro3 = IndikatorMakro::create([
            'nama_indikator' => 'Tingkat Kemiskinan',
            'indikator_bidang_id' => $bidang->id,
            'is_active' => true
        ]);
        $makro4 = IndikatorMakro::create([
            'nama_indikator' => 'Gini Ratio',
            'indikator_bidang_id' => $bidang->id,
            'is_active' => true
        ]);

        // Access without authentication
        $response = $this->getJson(route('indikator-makro.search', ['q' => 'Manusia']));
        $response->assertStatus(401);

        // Access with authentication
        $response = $this->actingAs($user)->getJson(route('indikator-makro.search', ['q' => 'Manusia']));
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $makro1->id,
            'nama_indikator' => 'Indeks Pembangunan Manusia'
        ]);

        // Search with empty query should return at most 3 results
        $response = $this->actingAs($user)->getJson(route('indikator-makro.search'));
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_can_reorder_indikator_dimensis_sets_updated_by()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);
        $dim1 = Dimensi::create(['nama_dimensi' => 'D1']);
        $dim2 = Dimensi::create(['nama_dimensi' => 'D2']);
        
        $id1 = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dim1->id,
            'urutan' => 1,
            'is_active' => true
        ]);
        $id2 = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dim2->id,
            'urutan' => 2,
            'is_active' => true
        ]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.indikator-dimensi.reorder'), [
            'order' => [$id2->id, $id1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, $id2->refresh()->urutan);
        $this->assertEquals(2, $id1->refresh()->urutan);
        $this->assertEquals($user->id, $id2->refresh()->updated_by);
        $this->assertEquals($user->id, $id1->refresh()->updated_by);
    }

    public function test_can_store_formatted_values_with_dots_and_commas()
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

        // Post standard US format: 54,748,977.00
        $response = $this->actingAs($user)->post(route('indikator-makro.store-nilai'), [
            'periode_indikator_id' => $periode->id,
            'indikator_makro_id' => $makro->id,
            'nilai' => [
                $user->kabupaten_id => [
                    $indDim->id => '54,748,977.00'
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nilai_indikator_makros', [
            'periode_indikator_id' => $periode->id,
            'kabupaten_id' => $user->kabupaten_id,
            'indikator_dimensi_id' => $indDim->id,
            'nilai' => 54748977.00
        ]);

        // Post Indonesian format: 54.748.977,00
        $response = $this->actingAs($user)->post(route('indikator-makro.store-nilai'), [
            'periode_indikator_id' => $periode->id,
            'indikator_makro_id' => $makro->id,
            'nilai' => [
                $user->kabupaten_id => [
                    $indDim->id => '54.748.977,00'
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nilai_indikator_makros', [
            'periode_indikator_id' => $periode->id,
            'kabupaten_id' => $user->kabupaten_id,
            'indikator_dimensi_id' => $indDim->id,
            'nilai' => 54748977.00
        ]);
    }

    public function test_can_store_all_user_format_variations()
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

        $variations = [
            // Standard US Formats
            '9,206.00' => 9206.00,
            '111.53' => 111.53,
            '72.88' => 72.88,
            '7.93' => 7.93,
            '4,685,371.26' => 4685371.26,
            '13,172' => 13172.00,
            '11,377,934.44' => 11377934.44,

            // Indonesian Formats
            '9.206,00' => 9206.00,
            '111,53' => 111.53,
            '72,88' => 72.88,
            '7,93' => 7.93,
            '4.685.371,26' => 4685371.26,
            '13.172' => 13172.00,
            '11.377.934,44' => 11377934.44,
        ];

        foreach ($variations as $raw => $expected) {
            $response = $this->actingAs($user)->post(route('indikator-makro.store-nilai'), [
                'periode_indikator_id' => $periode->id,
                'indikator_makro_id' => $makro->id,
                'nilai' => [
                    $user->kabupaten_id => [
                        $indDim->id => $raw
                    ]
                ]
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('nilai_indikator_makros', [
                'periode_indikator_id' => $periode->id,
                'kabupaten_id' => $user->kabupaten_id,
                'indikator_dimensi_id' => $indDim->id,
                'nilai' => $expected
            ]);
        }
    }

    public function test_rendered_values_are_formatted_in_us_format()
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

        // Create an existing value
        \App\Models\NilaiIndikatorMakro::create([
            'periode_indikator_id' => $periode->id,
            'kabupaten_id' => $user->kabupaten_id,
            'indikator_dimensi_id' => $indDim->id,
            'nilai' => 4685371.2600,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('indikator-makro.input-nilai', [
            'periode_indikator_id' => $periode->id,
            'indikator_makro_id' => $makro->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('value="4,685,371.26"', false);
    }

    public function test_can_toggle_indikator_dimensi_active_status()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Sosial']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'IPM', 'indikator_bidang_id' => $bidang->id]);
        $dim = Dimensi::create(['nama_dimensi' => 'D1']);
        $indDim = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dim->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($user)->patch(route('indikator-makro.indikator-dimensi.toggle', $indDim->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false
        ]);

        $this->assertFalse((bool)$indDim->refresh()->is_active);
    }

    public function test_makro_urutan_scoped_by_bidang_and_resequenced_on_delete()
    {
        $user = $this->createProvinceUser();
        $bidang4 = IndikatorBidang::create(['nama_bidang' => 'Bidang 4']);
        $bidang5 = IndikatorBidang::create(['nama_bidang' => 'Bidang 5']);

        // 1. Add data for bidang 4: should be 1, 2, 3
        $makro1 = IndikatorMakro::create([
            'nama_indikator' => 'Indikator 1 Bidang 4',
            'indikator_bidang_id' => $bidang4->id,
            'urutan' => 1
        ]);
        $makro2 = IndikatorMakro::create([
            'nama_indikator' => 'Indikator 2 Bidang 4',
            'indikator_bidang_id' => $bidang4->id,
            'urutan' => 2
        ]);
        $makro3 = IndikatorMakro::create([
            'nama_indikator' => 'Indikator 3 Bidang 4',
            'indikator_bidang_id' => $bidang4->id,
            'urutan' => 3
        ]);

        // 2. Add data for bidang 5: should be 1, 2, 3
        $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang5->id,
            'nama_indikator' => 'Indikator 1 Bidang 5',
            'is_active' => '1'
        ]);
        $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang5->id,
            'nama_indikator' => 'Indikator 2 Bidang 5',
            'is_active' => '1'
        ]);
        $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang5->id,
            'nama_indikator' => 'Indikator 3 Bidang 5',
            'is_active' => '1'
        ]);

        // Verify bidang 5 orders are 1, 2, 3
        $b5Makros = IndikatorMakro::where('indikator_bidang_id', $bidang5->id)->orderBy('id', 'asc')->get();
        $this->assertEquals(1, $b5Makros[0]->urutan);
        $this->assertEquals(2, $b5Makros[1]->urutan);
        $this->assertEquals(3, $b5Makros[2]->urutan);

        // 3. Add more data for bidang 4: should be 4
        $this->actingAs($user)->post(route('indikator-makro.makro.store'), [
            'indikator_bidang_id' => $bidang4->id,
            'nama_indikator' => 'Indikator 4 Bidang 4',
            'is_active' => '1'
        ]);
        $makro4 = IndikatorMakro::where('nama_indikator', 'Indikator 4 Bidang 4')->first();
        $this->assertEquals(4, $makro4->urutan);

        // 4. Delete bidang 4 at order 2 (makro2)
        $this->actingAs($user)->delete(route('indikator-makro.makro.destroy', $makro2->id));

        // The remaining should be reordered to 1, 2, 3
        $this->assertEquals(1, $makro1->refresh()->urutan);
        $this->assertEquals(2, $makro3->refresh()->urutan);
        $this->assertEquals(3, $makro4->refresh()->urutan);
    }

    public function test_kelola_search_features()
    {
        $user = $this->createProvinceUser();

        // 1. Setup Bidang & Makro & Dimensi & IndikatorDimensi
        $bidangA = IndikatorBidang::create(['nama_bidang' => 'Bidang Ekonomi']);
        $bidangB = IndikatorBidang::create(['nama_bidang' => 'Bidang Sosial']);

        $makroA = IndikatorMakro::create([
            'nama_indikator' => 'Laju Inflasi',
            'indikator_bidang_id' => $bidangA->id,
            'urutan' => 1
        ]);
        $makroB = IndikatorMakro::create([
            'nama_indikator' => 'Tingkat Kemiskinan',
            'indikator_bidang_id' => $bidangB->id,
            'urutan' => 1
        ]);

        $dimensiA = Dimensi::create(['nama_dimensi' => 'Perkotaan']);
        $dimensiB = Dimensi::create(['nama_dimensi' => 'Pedesaan']);

        $indDimA = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makroB->id,
            'dimensi_id' => $dimensiA->id,
            'urutan' => 1,
            'is_active' => true
        ]);
        $indDimB = \App\Models\IndikatorDimensi::create([
            'indikator_makro_id' => $makroB->id,
            'dimensi_id' => $dimensiB->id,
            'urutan' => 2,
            'is_active' => true
        ]);

        // 2. Test Bidang Search
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'bidang',
            'search_bidang' => 'Ekonomi'
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('indikatorBidangs', function ($items) {
            return $items->count() === 1 && $items->first()->nama_bidang === 'Bidang Ekonomi';
        });

        // 3. Test Makro Search
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'makro',
            'filter_bidang_id' => $bidangA->id,
            'search_makro' => 'Inflasi'
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('indikatorMakros', function ($items) {
            return $items->count() === 1 && $items->first()->nama_indikator === 'Laju Inflasi';
        });

        // 4. Test Dimensi Search
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'dimensi',
            'search_dimensi' => 'Pedesaan'
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('dimensis', function ($items) {
            return $items->count() === 1 && $items->first()->nama_dimensi === 'Pedesaan';
        });

        // 5. Test Indikator Dimensi Search
        $response = $this->actingAs($user)->get(route('indikator-makro.kelola', [
            'tab' => 'indikator-dimensi',
            'filter_makro_id' => $makroB->id,
            'search_ind_dimensi' => 'Perkotaan'
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('indikatorDimensis', function ($items) {
            return $items->count() === 1 && $items->first()->dimensi->nama_dimensi === 'Perkotaan';
        });
    }

    public function test_manual_urutan_shifting_bidang()
    {
        $user = $this->createProvinceUser();

        $bidang1 = IndikatorBidang::create(['nama_bidang' => 'Bidang 1', 'urutan' => 1]);
        $bidang2 = IndikatorBidang::create(['nama_bidang' => 'Bidang 2', 'urutan' => 2]);
        $bidang3 = IndikatorBidang::create(['nama_bidang' => 'Bidang 3', 'urutan' => 3]);

        // Shift Bidang 3 to urutan 1 (should shift 1 & 2 down)
        $response = $this->actingAs($user)->put(route('indikator-makro.bidang.update', $bidang3->id), [
            'nama_bidang' => 'Bidang 3 Baru',
            'urutan' => 1
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $bidang3->refresh()->urutan);
        $this->assertEquals(2, $bidang1->refresh()->urutan);
        $this->assertEquals(3, $bidang2->refresh()->urutan);
    }

    public function test_manual_urutan_shifting_makro_same_bidang()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Bidang X']);

        $makro1 = IndikatorMakro::create(['nama_indikator' => 'M1', 'indikator_bidang_id' => $bidang->id, 'urutan' => 1]);
        $makro2 = IndikatorMakro::create(['nama_indikator' => 'M2', 'indikator_bidang_id' => $bidang->id, 'urutan' => 2]);
        $makro3 = IndikatorMakro::create(['nama_indikator' => 'M3', 'indikator_bidang_id' => $bidang->id, 'urutan' => 3]);

        // Shift M3 to urutan 2 (should shift M2 to 3)
        $response = $this->actingAs($user)->put(route('indikator-makro.makro.update', $makro3->id), [
            'indikator_bidang_id' => $bidang->id,
            'nama_indikator' => 'M3 Updated',
            'urutan' => 2
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $makro1->refresh()->urutan);
        $this->assertEquals(2, $makro3->refresh()->urutan);
        $this->assertEquals(3, $makro2->refresh()->urutan);
    }

    public function test_manual_urutan_shifting_makro_change_bidang()
    {
        $user = $this->createProvinceUser();
        $bidangA = IndikatorBidang::create(['nama_bidang' => 'Bidang A']);
        $bidangB = IndikatorBidang::create(['nama_bidang' => 'Bidang B']);

        $makroA1 = IndikatorMakro::create(['nama_indikator' => 'MA1', 'indikator_bidang_id' => $bidangA->id, 'urutan' => 1]);
        $makroA2 = IndikatorMakro::create(['nama_indikator' => 'MA2', 'indikator_bidang_id' => $bidangA->id, 'urutan' => 2]);

        $makroB1 = IndikatorMakro::create(['nama_indikator' => 'MB1', 'indikator_bidang_id' => $bidangB->id, 'urutan' => 1]);
        $makroB2 = IndikatorMakro::create(['nama_indikator' => 'MB2', 'indikator_bidang_id' => $bidangB->id, 'urutan' => 2]);

        // Move MA2 to Bidang B at urutan 1
        $response = $this->actingAs($user)->put(route('indikator-makro.makro.update', $makroA2->id), [
            'indikator_bidang_id' => $bidangB->id,
            'nama_indikator' => 'MA2 moved',
            'urutan' => 1
        ]);

        $response->assertRedirect();
        
        // MA2 should be at urutan 1 in Bidang B
        $this->assertEquals($bidangB->id, $makroA2->refresh()->indikator_bidang_id);
        $this->assertEquals(1, $makroA2->urutan);
        
        // MB1 and MB2 should be shifted down to 2 and 3
        $this->assertEquals(2, $makroB1->refresh()->urutan);
        $this->assertEquals(3, $makroB2->refresh()->urutan);

        // Bidang A should be resequenced (MA1 stays at 1)
        $this->assertEquals(1, $makroA1->refresh()->urutan);
    }

    public function test_manual_urutan_shifting_indikator_dimensi_same_makro()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Bidang Test']);
        $makro = IndikatorMakro::create(['nama_indikator' => 'Makro Test', 'indikator_bidang_id' => $bidang->id, 'urutan' => 1]);

        $dimensi1 = Dimensi::create(['nama_dimensi' => 'Dimensi 1']);
        $dimensi2 = Dimensi::create(['nama_dimensi' => 'Dimensi 2']);
        $dimensi3 = Dimensi::create(['nama_dimensi' => 'Dimensi 3']);

        $indDim1 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makro->id, 'dimensi_id' => $dimensi1->id, 'urutan' => 1]);
        $indDim2 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makro->id, 'dimensi_id' => $dimensi2->id, 'urutan' => 2]);
        $indDim3 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makro->id, 'dimensi_id' => $dimensi3->id, 'urutan' => 3]);

        // Shift IndikatorDimensi 3 to urutan 2 (should shift IndikatorDimensi 2 to 3)
        $response = $this->actingAs($user)->put(route('indikator-makro.indikator-dimensi.update', $indDim3->id), [
            'indikator_makro_id' => $makro->id,
            'dimensi_id' => $dimensi3->id,
            'urutan' => 2
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $indDim1->refresh()->urutan);
        $this->assertEquals(2, $indDim3->refresh()->urutan);
        $this->assertEquals(3, $indDim2->refresh()->urutan);
    }

    public function test_manual_urutan_shifting_indikator_dimensi_change_makro()
    {
        $user = $this->createProvinceUser();
        $bidang = IndikatorBidang::create(['nama_bidang' => 'Bidang Test']);
        
        $makroA = IndikatorMakro::create(['nama_indikator' => 'Makro A', 'indikator_bidang_id' => $bidang->id, 'urutan' => 1]);
        $makroB = IndikatorMakro::create(['nama_indikator' => 'Makro B', 'indikator_bidang_id' => $bidang->id, 'urutan' => 2]);

        $dimensi1 = Dimensi::create(['nama_dimensi' => 'Dimensi 1']);
        $dimensi2 = Dimensi::create(['nama_dimensi' => 'Dimensi 2']);
        $dimensi3 = Dimensi::create(['nama_dimensi' => 'Dimensi 3']);

        $indDimA1 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makroA->id, 'dimensi_id' => $dimensi1->id, 'urutan' => 1]);
        $indDimA2 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makroA->id, 'dimensi_id' => $dimensi2->id, 'urutan' => 2]);

        $indDimB1 = \App\Models\IndikatorDimensi::create(['indikator_makro_id' => $makroB->id, 'dimensi_id' => $dimensi3->id, 'urutan' => 1]);

        // Move indDimA2 to Makro B at urutan 1
        $response = $this->actingAs($user)->put(route('indikator-makro.indikator-dimensi.update', $indDimA2->id), [
            'indikator_makro_id' => $makroB->id,
            'dimensi_id' => $dimensi2->id,
            'urutan' => 1
        ]);

        $response->assertRedirect();
        
        // indDimA2 should be at urutan 1 in Makro B
        $this->assertEquals($makroB->id, $indDimA2->refresh()->indikator_makro_id);
        $this->assertEquals(1, $indDimA2->urutan);
        
        // indDimB1 should be shifted to urutan 2
        $this->assertEquals(2, $indDimB1->refresh()->urutan);

        // Makro A should be resequenced (indDimA1 stays at 1)
        $this->assertEquals(1, $indDimA1->refresh()->urutan);
    }
}



