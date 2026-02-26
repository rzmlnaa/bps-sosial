<?php

namespace Database\Factories;

use App\Models\Fenomena;
use App\Models\SumberBerita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fenomena>
 */
class FenomenaFactory extends Factory
{
    protected $model = Fenomena::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_berita' => $this->faker->date(),
            'bulan' => $this->faker->month(),
            'tahun' => $this->faker->year(),
            'judul' => $this->faker->sentence(),
            'penjelasan' => $this->faker->paragraph(),
            'link_berita' => $this->faker->url(),
            'sumber_berita_id' => SumberBerita::factory(),
            'status_verifikasi' => 'N',
            'created_by' => User::factory(),
        ];
    }
}
