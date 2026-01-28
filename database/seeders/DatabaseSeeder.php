<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Rezza Maulana',
            'email' => 'reza.kocoy@gmail.com',
            'no_hp' => '6285945622246',
            'no_hp_verified_at' => now(),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}
