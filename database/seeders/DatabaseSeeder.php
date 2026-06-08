<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AiPromptSeeder::class);
        $this->call(ScoreClassificationSeeder::class);
        $this->call(RatingObligasiSeeder::class);
        $this->call(YtmNormalCurveSeeder::class);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'is_member' => false,
        ]);

        User::factory()->create([
            'name' => 'User Biasa',
            'email' => 'user@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'user',
            'is_member' => false,
        ]);

        User::factory()->create([
            'name' => 'User Member',
            'email' => 'member@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'user',
            'is_member' => true,
        ]);

        $this->call(LocalTestDataSeeder::class);
    }
}
