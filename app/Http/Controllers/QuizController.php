<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function index()
    {
        $questions = Question::with('options')->orderBy('order')->get();
        $result = QuizResult::where('user_id', Auth::id())->latest()->first();
        return view('quiz.index', compact('questions', 'result'));
    }

    public function submit(Request $request)
    {
        $questions = Question::with('options')->orderBy('order')->get();

        $request->validate([
            'answers' => 'required|array|size:' . $questions->count(),
            'answers.*' => 'required|integer|exists:question_options,id',
        ]);

        $totalScore = 0;
        $answers = [];

        foreach ($request->answers as $questionId => $optionId) {
            $option = QuestionOption::find($optionId);
            if ($option && $option->question_id == $questionId) {
                $totalScore += $option->points;
                $answers[$questionId] = $optionId;
            }
        }

        $profile = QuizResult::profileFromScore($totalScore);

        QuizResult::updateOrCreate(
            ['user_id' => Auth::id()],
            ['total_score' => $totalScore, 'profile' => $profile, 'answers' => $answers]
        );

        return redirect()->route('quiz.result');
    }

    public function result()
    {
        $result = QuizResult::where('user_id', Auth::id())->latest()->first();

        if (!$result) {
            return redirect()->route('quiz.index');
        }

        $allocation = QuizResult::allocationFromProfile($result->profile);
        return view('quiz.result', compact('result', 'allocation'));
    }
}
