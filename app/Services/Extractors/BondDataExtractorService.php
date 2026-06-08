<?php

namespace App\Services\Extractors;

class BondDataExtractorService implements ExtractorInterface
{
    public function __construct(
        private readonly PheiBondExtractorService $pheiBondExtractor,
        private readonly IdxBondExtractorService $idxBondExtractor,
    ) {
    }

    public function extract(array $parameters): array
    {
        $errors = [];
        $selectedCodes = array_values(array_filter(array_map('strtoupper', $parameters['codes'] ?? [])));

        try {
            $rows = $this->pheiBondExtractor->extract($parameters);
            if (!empty($selectedCodes)) {
                $rows = array_values(array_filter($rows, fn (array $row) => in_array(strtoupper($row['bond_code'] ?? ''), $selectedCodes, true)));
            }
            if (!empty($rows)) {
                return [
                    'rows' => $rows,
                    'source' => 'PHEI',
                    'errors' => [],
                ];
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        try {
            $rows = $this->idxBondExtractor->extract($parameters);
            if (!empty($selectedCodes)) {
                $rows = array_values(array_filter($rows, fn (array $row) => in_array(strtoupper($row['bond_code'] ?? ''), $selectedCodes, true)));
            }
            if (!empty($rows)) {
                return [
                    'rows' => $rows,
                    'source' => 'IDX',
                    'errors' => $errors,
                ];
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        throw new \RuntimeException('Gagal mengekstrak data obligasi dari PHEI dan IDX: ' . implode(' | ', $errors));
    }
}
