<?php

namespace App\Imports\Sheets;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class PreviewSheet implements ToCollection, WithHeadingRow
{
    public array $rows = [];

    public function collection(Collection $rows): void
    {
        $this->rows = $rows
            ->filter(fn($r) => $r->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty())
            ->map(fn($r) => $r->toArray())
            ->values()
            ->toArray();
    }
}
