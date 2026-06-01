<?php

namespace App\Services;

use App\Models\LapkeuPdfExtraction;
use App\Models\ObligasiBond;

class FinancialDataResolverService
{
    public const FIELDS = [
        'total_asset' => 'total_asset',
        'total_liabilities' => 'total_liabilities',
        'total_equity' => 'equity',
        'revenue' => 'net_revenue',
        'net_income' => 'net_income',
    ];

    /**
     * Resolve the minimum financial data required by Obligasi AI Plus.
     *
     * @param  int|string|null  $obligationId  ObligasiBond id or issuer code
     * @param  array<string, mixed>  $context
     * @return array<string, array{value: mixed, source: string|null}>
     */
    public function resolveObligationData($obligationId, array $context = []): array
    {
        $draft = $context['draft'] ?? [];
        $draftSources = $context['sources'] ?? [];
        $pdfData = $this->pdfData($context['pdf_path'] ?? null);
        $master = $this->masterData($obligationId, $context['periode'] ?? null);

        $resolved = [];
        foreach (self::FIELDS as $publicField => $internalField) {
            $resolved[$publicField] = $this->firstAvailable([
                ['value' => $this->draftValue($draft, $draftSources, $internalField, 'input_manual'), 'source' => 'input_manual'],
                ['value' => $this->draftValue($draft, $draftSources, $internalField, 'upload_excel'), 'source' => 'upload_excel'],
                ['value' => $this->draftValue($draft, $draftSources, $internalField, 'pdf_lapkeu') ?? ($pdfData[$internalField] ?? null), 'source' => 'pdf_lapkeu'],
                ['value' => $master?->{$internalField}, 'source' => 'master_obligasi'],
            ]);
        }

        return $resolved;
    }

    public function isComplete(array $resolvedData): bool
    {
        return $this->missingFields($resolvedData) === [];
    }

    /**
     * @return list<string>
     */
    public function missingFields(array $resolvedData): array
    {
        return collect(array_keys(self::FIELDS))
            ->filter(fn (string $field) => !$this->available($resolvedData[$field]['value'] ?? null))
            ->values()
            ->all();
    }

    /**
     * Convert public resolver keys back to the existing lapkeu field names.
     *
     * @return array<string, mixed>
     */
    public function toAnalysisData(array $resolvedData): array
    {
        $data = [];
        foreach (self::FIELDS as $publicField => $internalField) {
            $data[$internalField] = $resolvedData[$publicField]['value'] ?? null;
        }

        return $data;
    }

    private function draftValue(array $draft, array $sources, string $field, string $expectedSource): mixed
    {
        $value = $draft[$field] ?? null;
        if (!$this->available($value)) {
            return null;
        }

        $source = $sources[$field] ?? 'input_manual';

        return $source === $expectedSource ? $value : null;
    }

    private function masterData($obligationId, ?string $periode): ?ObligasiBond
    {
        if (!$obligationId) {
            return null;
        }

        $query = ObligasiBond::query();
        if (is_numeric($obligationId)) {
            $query->whereKey($obligationId);
        } else {
            $query->whereRaw('UPPER(kode) = ?', [strtoupper(trim((string) $obligationId))]);
        }

        if ($periode) {
            $query->where('periode', $periode);
        }

        return $query->orderByDesc('periode')->first();
    }

    private function pdfData(?string $pdfPath): array
    {
        if (!$pdfPath) {
            return [];
        }

        return LapkeuPdfExtraction::query()
            ->where('instrumen', 'Obligasi')
            ->where('file_path', 'like', '%' . basename($pdfPath))
            ->where('status', 'completed')
            ->latest()
            ->value('result_data') ?? [];
    }

    private function firstAvailable(array $candidates): array
    {
        foreach ($candidates as $candidate) {
            if ($this->available($candidate['value'])) {
                return $candidate;
            }
        }

        return ['value' => null, 'source' => null];
    }

    private function available(mixed $value): bool
    {
        return $value !== null && $value !== '' && is_numeric($value);
    }
}
