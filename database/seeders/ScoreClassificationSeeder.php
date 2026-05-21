<?php

namespace Database\Seeders;

use App\Models\ScoreClassification;
use Illuminate\Database\Seeder;

class ScoreClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['profile_name' => 'Conservative',  'min_score' => 1,  'max_score' => 25, 'alloc_pasar_uang' => 60, 'alloc_pendapatan_tetap' => 30, 'alloc_campuran' => 10, 'alloc_saham' => 0,  'sort_order' => 1],
            ['profile_name' => 'Tolerant',       'min_score' => 26, 'max_score' => 50, 'alloc_pasar_uang' => 30, 'alloc_pendapatan_tetap' => 40, 'alloc_campuran' => 20, 'alloc_saham' => 10, 'sort_order' => 2],
            ['profile_name' => 'Moderate',       'min_score' => 51, 'max_score' => 75, 'alloc_pasar_uang' => 10, 'alloc_pendapatan_tetap' => 30, 'alloc_campuran' => 30, 'alloc_saham' => 30, 'sort_order' => 3],
            ['profile_name' => 'Risk Taker',     'min_score' => 76, 'max_score' => 100,'alloc_pasar_uang' => 0,  'alloc_pendapatan_tetap' => 10, 'alloc_campuran' => 20, 'alloc_saham' => 70, 'sort_order' => 4],
        ];

        foreach ($data as $row) {
            ScoreClassification::firstOrCreate(
                ['profile_name' => $row['profile_name']],
                $row
            );
        }
    }
}
