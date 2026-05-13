<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SektorTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Sektor'; }

    public function headings(): array
    {
        return ['nama_sektor', 'bobot'];
    }

    public function array(): array
    {
        return [['Keuangan', 25.50], ['Energi', 15.00]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class EfekTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Efek'; }

    public function headings(): array
    {
        return ['kode_efek', 'nama_efek', 'sektor', 'bobot', 'kontribusi_kinerja', 'market_cap', 'top_10'];
    }

    public function array(): array
    {
        return [
            ['BBCA', 'Bank Central Asia Tbk', 'Keuangan', 10.50, 0.35, 950000000000000, 'Ya'],
            ['TLKM', 'Telkom Indonesia Tbk', 'Teknologi', 8.20, -0.12, 280000000000000, 'Ya'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class KinerjaTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Kinerja'; }

    public function headings(): array
    {
        return ['periode', 'return_pct'];
    }

    public function array(): array
    {
        return [
            ['2024-01-01', 1.25],
            ['2024-02-01', -0.50],
            ['2024-03-01', 2.10],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class ObligasiTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Obligasi'; }

    public function headings(): array
    {
        return ['kode_obligasi', 'nama_obligasi', 'bobot', 'durasi', 'rating'];
    }

    public function array(): array
    {
        return [
            ['FR0091', 'Obligasi Negara FR0091', 15.00, 7.50, 'AAA'],
            ['BBRI01', 'Obligasi BRI 2025', 8.00, 3.20, 'AA+'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class BankTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Bank'; }

    public function headings(): array
    {
        return ['nama_bank', 'bobot', 'car', 'npl', 'klasifikasi_risiko'];
    }

    public function array(): array
    {
        return [
            ['Bank BCA', 20.00, 25.50, 1.20, 'Rendah'],
            ['Bank Mandiri', 15.00, 21.30, 2.10, 'Rendah'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
