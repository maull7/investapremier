<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResult extends Model
{
    protected $fillable = ['user_id', 'total_score', 'profile', 'answers'];

    protected $casts = ['answers' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function profileFromScore(int $score): string
    {
        $classification = ScoreClassification::orderBy('sort_order')
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        if ($classification) {
            return $classification->profile_name;
        }

        // Fallback jika DB kosong
        return match(true) {
            $score <= 12 => 'Conservative',
            $score <= 20 => 'Tolerant',
            $score <= 28 => 'Moderate',
            default      => 'Risk Taker',
        };
    }

    public static function allocationFromProfile(string $profile): array
    {
        $classification = ScoreClassification::where('profile_name', $profile)->first();

        if ($classification) {
            return $classification->getAllocation();
        }

        // Fallback jika DB kosong
        return match($profile) {
            'Conservative' => ['Pasar Uang' => 90, 'Pendapatan Tetap' => 10, 'Campuran' => 0, 'Saham' => 0],
            'Tolerant'     => ['Pasar Uang' => 20, 'Pendapatan Tetap' => 70, 'Campuran' => 10, 'Saham' => 0],
            'Moderate'     => ['Pasar Uang' => 10, 'Pendapatan Tetap' => 20, 'Campuran' => 60, 'Saham' => 10],
            'Risk Taker'   => ['Pasar Uang' => 5,  'Pendapatan Tetap' => 0,  'Campuran' => 15, 'Saham' => 80],
            default        => [],
        };
    }
}
