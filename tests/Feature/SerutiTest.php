<?php

namespace Tests\Feature;

use App\Models\Coicop;
use App\Models\ConsumptionValue;
use App\Models\Kabupaten;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SerutiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $kabupaten6100;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Kabupaten for OnlyProvince6100 middleware

        $this->kabupaten6100 = Kabupaten::create([
            'kode_kab' => '6100',
            'nama_kabupaten' => 'PROVINSI KALIMANTAN BARAT'
        ]);

        // Setup User that can pass both middlewares
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'kabupaten_id' => $this->kabupaten6100->id,
            'status' => 'active',
        ]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /** @test */
    public function authenticated_user_can_access_seruti_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('seruti.index'));

        $response->assertStatus(200);
        $response->assertViewIs('seruti.index');
    }

    /** @test */
    public function user_can_store_consumption_data()
    {
        $coicop = Coicop::create(['kode' => '01', 'nama' => 'Makanan', 'seruti' => 'Makanan']);

        $payload = [
            'year' => 2024,
            'quarter' => 1,
            'kabupaten_id' => $this->kabupaten6100->id,
            'data' => [
                ['kode' => '01', 'value' => 50000]
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('seruti.store'), $payload);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('consumption_values', [
            'value' => 50000,
            'kabupaten_id' => $this->kabupaten6100->id
        ]);
    }

    /** @test */
    public function user_can_get_table_data()
    {
        $coicop = Coicop::create(['kode' => '01', 'nama' => 'Makanan', 'seruti' => 'Makanan']);
        $period = Period::create(['year' => 2024, 'quarter' => 1]);

        ConsumptionValue::create([
            'period_id' => $period->id,
            'coicop_id' => $coicop->id,
            'kabupaten_id' => $this->kabupaten6100->id,
            'value' => 75000
        ]);

        $response = $this->actingAs($this->user)->get(route('seruti.get-data', [
            'year' => 2024,
            'quarter' => 1,
            'kabupaten_id' => $this->kabupaten6100->id
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['value' => 75000]);
    }

    /** @test */
    public function it_detects_anomalies_in_chart_data()
    {
        $coicop = Coicop::create(['kode' => '01', 'nama' => 'Makanan', 'seruti' => 'Makanan']);
        $period = Period::create(['year' => 2024, 'quarter' => 1]);

        // Setup Kabupaten lain untuk rata2 provinsi
        $kab2 = Kabupaten::create(['kode_kab' => '6101', 'nama_kabupaten' => 'Kab 2']);

        // Avg = (100 + 200) / 2 = 150. 
        // 200 is ~33% higher than 150. default is 25%.
        ConsumptionValue::create(['period_id' => $period->id, 'coicop_id' => $coicop->id, 'kabupaten_id' => $kab2->id, 'value' => 100]);
        ConsumptionValue::create(['period_id' => $period->id, 'coicop_id' => $coicop->id, 'kabupaten_id' => $this->kabupaten6100->id, 'value' => 200]);

        $response = $this->actingAs($this->user)->get(route('seruti.chart-data', [
            'year' => 2024,
            'quarters' => '1',
            'kabupaten_id' => $this->kabupaten6100->id,
            'threshold' => 25
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('anomalies.0.type', 'Tinggi');
    }

    /** @test */
    public function user_can_store_coicop_master_data()
    {
        $payload = [
            'coicops' => [
                ['kode' => '99', 'nama' => 'Komoditas Baru', 'seruti' => 'Ya']
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('seruti.store-coicop'), $payload);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('coicops', ['kode' => '99']);
    }

    /** @test */
    public function user_cannot_delete_coicop_with_usage()
    {
        $coicop = Coicop::create(['kode' => '01', 'nama' => 'Makanan', 'seruti' => 'Makanan']);
        $period = Period::create(['year' => 2024, 'quarter' => 1]);

        ConsumptionValue::create([
            'period_id' => $period->id,
            'coicop_id' => $coicop->id,
            'kabupaten_id' => $this->kabupaten6100->id,
            'value' => 1000
        ]);

        $response = $this->actingAs($this->user)->delete(route('seruti.destroy-coicop', $coicop->id));

        $response->assertStatus(400);
        $this->assertDatabaseHas('coicops', ['id' => $coicop->id]);
    }

    /** @test */
    public function user_can_clear_consumption_data()
    {
        $period = Period::create(['year' => 2024, 'quarter' => 1]);
        $coicop = Coicop::create(['kode' => '01', 'nama' => 'Makanan', 'seruti' => 'Makanan']);

        ConsumptionValue::create([
            'period_id' => $period->id,
            'coicop_id' => $coicop->id,
            'kabupaten_id' => $this->kabupaten6100->id,
            'value' => 50000
        ]);

        $payload = [
            'year' => 2024,
            'quarter' => 1,
            'kabupaten_id' => $this->kabupaten6100->id
        ];

        $response = $this->actingAs($this->user)->delete(route('seruti.destroy-consumption'), $payload);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('consumption_values', ['kabupaten_id' => $this->kabupaten6100->id]);
    }
}
