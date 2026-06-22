<?php

namespace App\Services;

class ProspektusValidator
{
    private array $errors = [];
    private array $warnings = [];

    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->warnings = [];

        $this->validateFundInfo($data);
        $this->validateFinancialStatements($data);
        $this->validatePortfolio($data);
        $this->validatePerformance($data);
        $this->validateMiProfile($data);

        return empty($this->errors);
    }

    private function validateFundInfo(array $data): void
    {
        if (empty($data['nama_reksa_dana'])) {
            $this->errors[] = 'Nama Reksa Dana tidak ditemukan';
        } elseif (strlen($data['nama_reksa_dana']) < 3) {
            $this->errors[] = 'Nama Reksa Dana terlalu pendek';
        }

        if (!empty($data['total_aum']) && $data['total_aum'] < 0) {
            $this->errors[] = 'Total AUM tidak valid (negatif)';
        }

        if (!empty($data['nab_per_unit']) && $data['nab_per_unit'] < 0) {
            $this->errors[] = 'NAB/UP tidak valid (negatif)';
        }
    }

    private function validateFinancialStatements(array $data): void
    {
        if (empty($data['total_aset']) && empty($data['total_liabilitas'])) {
            return;
        }

        if (!empty($data['total_aset']) && !empty($data['total_liabilitas'])) {
            if ($data['total_aset'] < $data['total_liabilitas']) {
                $this->warnings[] = 'Total aset lebih kecil dari total liabilitas (ekuitas negatif)';
            }
        }

        if (!empty($data['piutang_bunga']) && $data['piutang_bunga'] < 0) {
            $this->warnings[] = 'Piutang bunga bernilai negatif';
        }

        if (!empty($data['pendapatan_bunga']) && !empty($data['beban_mi'])) {
            if ($data['pendapatan_bunga'] < $data['beban_mi']) {
                $this->warnings[] = 'Pendapatan bunga lebih kecil dari beban MI';
            }
        }
    }

    private function validatePortfolio(array $data): void
    {
        if (!empty($data['alokasi_aset'])) {
            $total = array_sum(array_column($data['alokasi_aset'], 'persentase'));
            if ($total < 90 || $total > 110) {
                if ($total > 0) {
                    $this->warnings[] = "Total alokasi aset ({$total}%) tidak mendekati 100%";
                }
            }
        }

        if (!empty($data['sektor'])) {
            $total = array_sum(array_column($data['sektor'], 'bobot'));
            if ($total < 90 || $total > 110) {
                if ($total > 0) {
                    $this->warnings[] = "Total bobot sektor ({$total}%) tidak mendekati 100%";
                }
            }
        }
    }

    private function validatePerformance(array $data): void
    {
        $returnFields = ['return_ytd', 'return_1y', 'total_return'];
        foreach ($returnFields as $field) {
            if (!empty($data[$field]) && ($data[$field] > 1000 || $data[$field] < -100)) {
                $this->warnings[] = "{$field} ({$data[$field]}%) berada di luar kisaran wajar";
            }
        }
    }

    private function validateMiProfile(array $data): void
    {
        if (empty($data['manajer_investasi'])) {
            $this->warnings[] = 'Manajer Investasi tidak ditemukan';
        }

        if (empty($data['bank_kustodian'])) {
            $this->warnings[] = 'Bank Kustodian tidak ditemukan';
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}
