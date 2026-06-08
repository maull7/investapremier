<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Exports\QuestionsTemplateExport;
use App\Imports\QuestionsImport;
use App\Support\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $questions = Question::with('options')->orderBy('order')->paginate($perPage)->withQueryString();
        return view('admin.questions.index', compact('questions', 'perPage'));
    }

    public function create()
    {
        return view('admin.questions.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'options'       => 'required|array|size:4',
            'options.*.label'       => 'required|in:A,B,C,D',
            'options.*.option_text' => 'required|string',
            'options.*.points'      => 'required|integer|min:1',
        ]);

        $order = Question::max('order') + 1;
        $question = Question::create(['question_text' => $request->question_text, 'order' => $order]);

        foreach ($request->options as $opt) {
            $question->options()->create($opt);
        }

        ActivityLogger::log(
            'Membuat Soal',
            "Soal kuis berhasil ditambahkan: ".str($request->question_text)->limit(100),
            'success',
            $question,
        );

        return redirect()->route('admin.questions.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Question $question)
    {
        $question->load('options');
        return view('admin.questions.form', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'options'       => 'required|array|size:4',
            'options.*.label'       => 'required|in:A,B,C,D',
            'options.*.option_text' => 'required|string',
            'options.*.points'      => 'required|integer|min:1',
        ]);

        $question->update(['question_text' => $request->question_text]);
        $question->options()->delete();

        foreach ($request->options as $opt) {
            $question->options()->create($opt);
        }

        ActivityLogger::log(
            'Mengubah Soal',
            "Soal kuis berhasil diperbarui: ".str($request->question_text)->limit(100),
            'success',
            $question,
        );

        return redirect()->route('admin.questions.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new QuestionsTemplateExport, 'template-soal-kuis.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new QuestionsImport;
        Excel::import($import, $request->file('file'));

        ActivityLogger::log(
            'Import Soal',
            "{$import->imported} soal berhasil diimport dari Excel",
            'success',
        );

        return redirect()->route('admin.questions.index')
            ->with('success', "{$import->imported} soal berhasil diimport dari Excel.");
    }

    public function destroy(Question $question)
    {
        ActivityLogger::log(
            'Menghapus Soal',
            "Soal kuis berhasil dihapus: ".str($question->question_text)->limit(100),
            'success',
            $question,
        );
        $question->delete();
        return redirect()->route('admin.questions.index')->with('success', 'Soal berhasil dihapus.');
    }
}
