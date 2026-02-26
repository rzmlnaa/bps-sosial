<?php

namespace Database\Factories;

use App\Models\SumberBerita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SumberBerita>
 */
class SumberBeritaFactory extends Factory
{
    protected $model = SumberBerita::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->company(),
            'is_online' => $this->faker->boolean(),
        ];
    }
}
