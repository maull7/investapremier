<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        $order = Question::max('order') ?? 0;

        foreach ($rows as $row) {
            if (empty($row['question_text'])) continue;

            $order++;
            $question = Question::create(['question_text' => $row['question_text'], 'order' => $order]);

            foreach (['a', 'b', 'c', 'd'] as $label) {
                $text   = $row["option_{$label}_text"]   ?? null;
                $points = $row["option_{$label}_points"] ?? null;

                if ($text && $points) {
                    $question->options()->create([
                        'label'       => strtoupper($label),
                        'option_text' => $text,
                        'points'      => (int) $points,
                    ]);
                }
            }

            $this->imported++;
        }
    }
}
