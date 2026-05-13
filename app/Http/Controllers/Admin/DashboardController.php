<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalisaReksaDana;
use App\Models\MemberProfile;
use App\Models\Question;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik existing
        $totalUsers       = User::count();
        $totalMembers     = User::where('is_member', true)->count();
        $totalAdmins      = User::where('role', 'admin')->count();
        $totalQuestions   = Question::count();
        $totalQuizResults = QuizResult::count();
        $pendingMembers   = MemberProfile::where('status', 'pending')->count();

        $recentUsers      = User::latest()->take(5)->get();
        $recentApplicants = MemberProfile::with('user')->latest()->take(5)->get();

        $quizProfileDistribution = QuizResult::selectRaw('profile, COUNT(*) as total')
            ->groupBy('profile')->pluck('total', 'profile');

        $profileOrder = ['Conservative', 'Tolerant', 'Moderate', 'Risk Taker'];
        $profileStats = collect($profileOrder)->map(fn($p) => [
            'profile' => $p,
            'total'   => (int) ($quizProfileDistribution[$p] ?? 0),
        ]);

        // Statistik analisa
        $totalAnalisa    = AnalisaReksaDana::count();
        $analisaPending  = AnalisaReksaDana::where('status', 'submitted')->count();
        $analisaReviewed = AnalisaReksaDana::where('status', 'reviewed')->count();

        // Submission analisa per bulan (6 bulan terakhir)
        $analisaPerBulan = AnalisaReksaDana::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        // Isi bulan yang kosong
        $bulanLabels = collect();
        for ($i = 5; $i >= 0; $i--) {
            $bulanLabels->put(now()->subMonths($i)->format('Y-m'), 0);
        }
        $analisaPerBulan = $bulanLabels->merge($analisaPerBulan);

        // Distribusi jenis RD
        $analisaPerJenis = AnalisaReksaDana::selectRaw('jenis_reksa_dana, COUNT(*) as total')
            ->groupBy('jenis_reksa_dana')
            ->pluck('total', 'jenis_reksa_dana');

        // Submission terbaru
        $recentAnalisa = AnalisaReksaDana::with('user')->latest()->take(5)->get();

        return view('dashboard.admin', compact(
            'totalUsers', 'totalMembers', 'totalAdmins',
            'totalQuestions', 'totalQuizResults', 'pendingMembers',
            'recentUsers', 'recentApplicants', 'profileStats',
            'totalAnalisa', 'analisaPending', 'analisaReviewed',
            'analisaPerBulan', 'analisaPerJenis', 'recentAnalisa',
        ));
    }
}
