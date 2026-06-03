<?php

namespace App\Services;

use App\Models\AnalisaObligasiKeuangan;
use App\Models\RatingObligasi;
use App\Models\YtmNormalCurve;

class ShadowRatingService
{
    private array $weights = [
        'current_ratio' => 10,
        'debt_to_equity' => 15,
        'interest_coverage' => 15,
        'net_profit_margin' => 10,
        'roa' => 15,
        'roe' => 15,
        'debt_to_asset' => 10,
        'operating_cash_flow_ratio' => 10,
    ];

    private array $ratingTiers = [
        ['min' => 90, 'rating' => 'AAA', 'confidence' => 95],
        ['min' => 82, 'rating' => 'AA+', 'confidence' => 90],
        ['min' => 75, 'rating' => 'AA', 'confidence' => 85],
        ['min' => 68, 'rating' => 'AA-', 'confidence' => 80],
        ['min' => 62, 'rating' => 'A+', 'confidence' => 75],
        ['min' => 56, 'rating' => 'A', 'confidence' => 70],
        ['min' => 50, 'rating' => 'A-', 'confidence' => 65],
        ['min' => 44, 'rating' => 'BBB+', 'confidence' => 60],
        ['min' => 38, 'rating' => 'BBB', 'confidence' => 55],
        ['min' => 32, 'rating' => 'BBB-', 'confidence' => 50],
        ['min' => 26, 'rating' => 'BB+', 'confidence' => 45],
        ['min' => 21, 'rating' => 'BB', 'confidence' => 40],
        ['min' => 16, 'rating' => 'BB-', 'confidence' => 35],
        ['min' => 11, 'rating' => 'B+', 'confidence' => 30],
        ['min' => 6, 'rating' => 'B', 'confidence' => 25],
        ['min' => 0, 'rating' => 'CCC', 'confidence' => 20],
    ];

    public function calculate(AnalisaObligasiKeuangan $analisa): array
    {
        $ratios = $this->calculateRatios($analisa);
        $scores = $this->scoreRatios($ratios);
        $weightedScore = $this->calculateWeightedScore($scores);
        $mapping = $this->mapScoreToRating($weightedScore);
        $confidence = $this->calculateConfidence($analisa);

        return [
            'shadow_rating' => $mapping['rating'],
            'shadow_score' => round($weightedScore, 2),
            'shadow_confidence' => round(min($mapping['confidence'], $confidence), 2),
            'ratios' => $ratios,
            'scores' => $scores,
        ];
    }

    public function calculateYtmSpread(AnalisaObligasiKeuangan $analisa): array
    {
        $result = [
            'ytm_normal' => null,
            'ytm_spread' => null,
        ];

        $rating = $analisa->official_rating ?? $analisa->rating ?? $analisa->shadow_rating;
        $tenor = $analisa->tenor_bulan;

        if (!$rating || !$tenor || !$analisa->ytm) {
            return $result;
        }

        $ratingModel = RatingObligasi::where('kode', $rating)->first();
        if (!$ratingModel) {
            return $result;
        }

        $curve = YtmNormalCurve::where('rating_id', $ratingModel->id)
            ->orderByRaw('ABS(tenor_bulan - ?)', [$tenor])
            ->first();

        if (!$curve) {
            return $result;
        }

        $ytmNormal = $curve->ytm_normal;
        $ytmSpread = (float) $analisa->ytm - (float) $ytmNormal;

        return [
            'ytm_normal' => $ytmNormal,
            'ytm_spread' => round($ytmSpread, 4),
        ];
    }

    private function calculateRatios(AnalisaObligasiKeuangan $analisa): array
    {
        $ca = (float) ($analisa->current_asset ?? 0);
        $cl = (float) ($analisa->current_liabilities ?? 0);
        $tl = (float) ($analisa->total_liabilities ?? 0);
        $eq = (float) ($analisa->equity ?? 0);
        $ebit = (float) ($analisa->ebit ?? 0);
        $interest = (float) ($analisa->interest_expense ?? 0);
        $ni = (float) ($analisa->net_income ?? 0);
        $rev = (float) ($analisa->net_revenue ?? 0);
        $ta = (float) ($analisa->total_asset ?? 0);
        $cfo = (float) ($analisa->cash_flows_operating_activities ?? 0);

        return [
            'current_ratio' => $cl > 0 ? round($ca / $cl, 4) : null,
            'debt_to_equity' => $eq > 0 ? round($tl / $eq, 4) : null,
            'interest_coverage' => $interest > 0 ? round($ebit / $interest, 4) : null,
            'net_profit_margin' => $rev > 0 ? round(($ni / $rev) * 100, 4) : null,
            'roa' => $ta > 0 ? round(($ni / $ta) * 100, 4) : null,
            'roe' => $eq > 0 ? round(($ni / $eq) * 100, 4) : null,
            'debt_to_asset' => $ta > 0 ? round($tl / $ta, 4) : null,
            'operating_cash_flow_ratio' => $cl > 0 ? round($cfo / $cl, 4) : null,
        ];
    }

    private function scoreRatios(array $ratios): array
    {
        return [
            'current_ratio' => $this->scoreCurrentRatio($ratios['current_ratio']),
            'debt_to_equity' => $this->scoreDer($ratios['debt_to_equity']),
            'interest_coverage' => $this->scoreInterestCoverage($ratios['interest_coverage']),
            'net_profit_margin' => $this->scoreNpm($ratios['net_profit_margin']),
            'roa' => $this->scoreRoa($ratios['roa']),
            'roe' => $this->scoreRoe($ratios['roe']),
            'debt_to_asset' => $this->scoreDebtToAsset($ratios['debt_to_asset']),
            'operating_cash_flow_ratio' => $this->scoreOcf($ratios['operating_cash_flow_ratio']),
        ];
    }

    private function calculateWeightedScore(array $scores): float
    {
        $totalWeight = 0;
        $totalScore = 0;

        foreach ($this->weights as $key => $weight) {
            if (isset($scores[$key]) && $scores[$key] !== null) {
                $totalScore += $scores[$key] * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return $totalScore / $totalWeight * 10;
    }

    private function mapScoreToRating(float $score): array
    {
        foreach ($this->ratingTiers as $tier) {
            if ($score >= $tier['min']) {
                return $tier;
            }
        }

        return ['rating' => 'D', 'confidence' => 10];
    }

    private function calculateConfidence(AnalisaObligasiKeuangan $analisa): float
    {
        $requiredFields = [
            'current_asset', 'current_liabilities', 'total_liabilities', 'equity',
            'ebit', 'interest_expense', 'net_income', 'net_revenue', 'total_asset',
            'cash_flows_operating_activities',
        ];

        $present = 0;
        foreach ($requiredFields as $field) {
            if ($analisa->{$field} !== null) {
                $present++;
            }
        }

        return round(($present / count($requiredFields)) * 100, 2);
    }

    private function scoreCurrentRatio(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 2.5) return 10;
        if ($value >= 2.0) return 9;
        if ($value >= 1.5) return 7;
        if ($value >= 1.2) return 5;
        if ($value >= 1.0) return 3;
        if ($value >= 0.8) return 2;
        return 1;
    }

    private function scoreDer(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value <= 0.3) return 10;
        if ($value <= 0.5) return 9;
        if ($value <= 1.0) return 8;
        if ($value <= 1.5) return 6;
        if ($value <= 2.0) return 4;
        if ($value <= 3.0) return 2;
        return 1;
    }

    private function scoreInterestCoverage(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 15) return 10;
        if ($value >= 10) return 9;
        if ($value >= 7) return 8;
        if ($value >= 5) return 6;
        if ($value >= 3) return 4;
        if ($value >= 1) return 2;
        return 1;
    }

    private function scoreNpm(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 30) return 10;
        if ($value >= 20) return 9;
        if ($value >= 15) return 8;
        if ($value >= 10) return 6;
        if ($value >= 5) return 4;
        if ($value >= 2) return 3;
        if ($value >= 0) return 2;
        return 1;
    }

    private function scoreRoa(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 15) return 10;
        if ($value >= 10) return 9;
        if ($value >= 7) return 8;
        if ($value >= 5) return 6;
        if ($value >= 3) return 4;
        if ($value >= 0) return 2;
        return 1;
    }

    private function scoreRoe(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 30) return 10;
        if ($value >= 20) return 9;
        if ($value >= 15) return 8;
        if ($value >= 10) return 6;
        if ($value >= 5) return 4;
        if ($value >= 0) return 2;
        return 1;
    }

    private function scoreDebtToAsset(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value <= 0.2) return 10;
        if ($value <= 0.3) return 9;
        if ($value <= 0.4) return 8;
        if ($value <= 0.5) return 6;
        if ($value <= 0.6) return 4;
        if ($value <= 0.8) return 2;
        return 1;
    }

    private function scoreOcf(?float $value): ?float
    {
        if ($value === null) return null;
        if ($value >= 1.5) return 10;
        if ($value >= 1.0) return 8;
        if ($value >= 0.5) return 6;
        if ($value >= 0.2) return 4;
        if ($value >= 0) return 3;
        if ($value >= -0.2) return 2;
        return 1;
    }
}
