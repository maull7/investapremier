<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['question_text', 'option_a_text', 'option_a_points', 'option_b_text', 'option_b_points', 'option_c_text', 'option_c_points', 'option_d_text', 'option_d_points'];
    }

    public function array(): array
    {
        return [
            ['Contoh: Apa tujuan investasi Anda?', 'Menjaga nilai uang', 1, 'Mendapat bunga stabil', 2, 'Pertumbuhan moderat', 3, 'Pertumbuhan maksimal', 4],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
