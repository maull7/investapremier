<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Shared Excel date utilities for Exports and Imports.
 *
 * EXPORT  – use excelDateValue() to convert a date string/Carbon to an Excel
 *           serial number, then apply DATE_FORMAT via columnDateFormats().
 *
 * IMPORT  – use parseExcelDate() which handles both Excel serial numbers
 *           and any string representation (yyyy-mm-dd, d-M-yyyy, etc.).
 */
trait ExcelDateHelper
{
    /** Number format applied to date columns in exported templates. */
    protected string $excelDateFormat = 'YYYY-MM-DD';

    /**
     * Convert a date value to an Excel serial number (float).
     * Returns null when the value is empty / unparseable.
     *
     * @param  string|int|float|\DateTimeInterface|null  $value
     */
    protected function excelDateValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return ExcelDate::PHPToExcel($value);
        }

        if (is_numeric($value)) {
            // Already an Excel serial – return as-is
            return (float) $value;
        }

        try {
            $dt = new \DateTime((string) $value);
            return ExcelDate::PHPToExcel($dt);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse an Excel cell value to a 'Y-m-d' string for database storage.
     * Handles:
     *   – Excel serial numbers (numeric)
     *   – ISO strings  "2026-06-03"
     *   – Locale strings "03-Jun-2026", "18-May-2026", etc.
     *
     * @param  string|int|float|null  $value
     */
    protected function parseExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial number
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        // String – try strtotime for common formats
        $ts = strtotime((string) $value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        // Fallback: Carbon
        try {
            return \Carbon\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Apply DATE_FORMAT number format to a specific cell.
     * Call from styles() or afterSheet() after writing date values.
     *
     * Usage in styles(Worksheet $sheet):
     *   $this->applyDateFormats($sheet, [
     *       'C' => 2,   // column C, starting row 2
     *   ]);
     */
    protected function applyDateFormats(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $columns, // ['C' => 2, 'D' => 2, ...]
        int $lastRow = 100
    ): void {
        foreach ($columns as $col => $startRow) {
            $sheet->getStyle("{$col}{$startRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2); // built-in YYYY-MM-DD
        }
    }

    /**
     * Return a WithColumnFormatting-compatible array for date columns.
     * Key = column letter, Value = format code.
     *
     * @param  string[]  $columnLetters  e.g. ['C', 'D']
     */
    protected function dateColumnFormats(array $columnLetters): array
    {
        return array_fill_keys(
            $columnLetters,
            NumberFormat::FORMAT_DATE_YYYYMMDD2
        );
    }
}
