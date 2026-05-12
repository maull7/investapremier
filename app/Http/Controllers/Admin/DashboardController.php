<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberProfile;
use App\Models\Question;
use App\Models\QuizResult;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalMembers = User::where('is_member', true)->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalQuestions = Question::count();
        $totalQuizResults = QuizResult::count();
        $pendingMembers = MemberProfile::where('status', 'pending')->count();

        $recentUsers = User::latest()->take(5)->get();
        $recentApplicants = MemberProfile::with('user')->latest()->take(5)->get();

        $quizProfileDistribution = QuizResult::selectRaw('profile, COUNT(*) as total')
            ->groupBy('profile')
            ->pluck('total', 'profile');

        $profileOrder = ['Conservative', 'Tolerant', 'Moderate', 'Risk Taker'];
        $profileStats = collect($profileOrder)->map(function ($profile) use ($quizProfileDistribution) {
            return [
                'profile' => $profile,
                'total' => (int) ($quizProfileDistribution[$profile] ?? 0),
            ];
        });

        return view('dashboard.admin', compact(
            'totalUsers',
            'totalMembers',
            'totalAdmins',
            'totalQuestions',
            'totalQuizResults',
            'pendingMembers',
            'recentUsers',
            'recentApplicants',
            'profileStats'
        ));
    }
}
