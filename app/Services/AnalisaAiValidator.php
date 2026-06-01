<?php

namespace App\Services;

use App\Models\AnalisaReksaDana;

class AnalisaAiValidator
{
    public const MSG_PLUS_HEADER = 'Data Input Lengkap belum lengkap. Lengkapi bagian berikut di tab Input Lengkap sebelum menjalankan Analisa AI Plus:';

    public static function hasPlusManualData(AnalisaReksaDana $analisa): bool
    {
        return empty(self::plusMissingSections($analisa));
    }

    /**
     * @return list<string>
     */
    public static function plusMissingSections(AnalisaReksaDana $analisa): array
    {
        $missing = [];

        if (!filled($analisa->total_aum)) {
            $missing[] = 'Total AUM';
        }

        if (!filled($analisa->total_marcap_10_efek)) {
            $missing[] = 'Total MarCap 10 efek terbesar';
        }

        $hasSektor = $analisa->sektor->contains(
            fn ($s) => filled($s->nama_sektor) && is_numeric($s->bobot)
        );
        if (!$hasSektor) {
            $missing[] = 'Komposisi sektor (minimal 1 baris dengan bobot %)';
        }

        $hasEfek = $analisa->efek->contains(
            fn ($e) => filled($e->kode_efek) && filled($e->nama_efek) && is_numeric($e->bobot)
        );
        if (!$hasEfek) {
            $missing[] = 'Daftar efek (minimal 1 baris: kode, nama, bobot %)';
        }

        return $missing;
    }

    public static function plusIncompleteMessage(AnalisaReksaDana $analisa): string
    {
        $missing = self::plusMissingSections($analisa);
        if ($missing === []) {
            return '';
        }

        return self::MSG_PLUS_HEADER."\n• ".implode("\n• ", $missing);
    }

    /**
     * @return array{can_run: bool, missing: list<string>, message: string}
     */
    public static function assessPlusManualData(AnalisaReksaDana $analisa): array
    {
        $missing = self::plusMissingSections($analisa);

        return [
            'can_run' => $missing === [],
            'missing' => $missing,
            'message' => $missing === [] ? '' : self::plusIncompleteMessage($analisa),
        ];
    }
}
