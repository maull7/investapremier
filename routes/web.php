<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\AnalisaController as AdminAnalisaController;
use App\Http\Controllers\Admin\ScoreClassificationController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnalisaController;
use App\Http\Controllers\Admin\ReksaDanaController as AdminReksaDanaController;
use App\Http\Controllers\Admin\AnalisaRdController as AdminAnalisaRdController;
use App\Http\Controllers\Admin\DaftarReksaDanaController;
use App\Http\Controllers\Admin\DataSourceLinkController;
use App\Http\Controllers\User\DataSourceLinkController as UserDataSourceLinkController;
use App\Http\Controllers\ReksaDanaController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);

Route::get('/presentation', function () {
    return view('index-presentation');
})->name('index.presentation');

Route::get('/dashboard', function () {
    $user = request()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('score-classifications', [ScoreClassificationController::class, 'index'])->name('score-classifications.index');
    Route::put('score-classifications/{scoreClassification}', [ScoreClassificationController::class, 'update'])->name('score-classifications.update');

    Route::resource('questions', QuestionController::class);
    Route::get('questions-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::post('questions-import', [QuestionController::class, 'import'])->name('questions.import');

    Route::get('members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::get('members/{member}', [AdminMemberController::class, 'show'])->name('members.show');
    Route::post('members/{member}/approve', [AdminMemberController::class, 'approve'])->name('members.approve');
    Route::post('members/{member}/reject', [AdminMemberController::class, 'reject'])->name('members.reject');

    // Monitor analisa yang diupload userr
    Route::get('analisa', [AdminAnalisaController::class, 'index'])->name('analisa.index');
    Route::get('analisa/{analisa}', [AdminAnalisaController::class, 'show'])->name('analisa.show');
    Route::get('analisa/{analisa}/pdf', [AdminAnalisaController::class, 'exportPdf'])->name('analisa.pdf');
    Route::get('analisa/{analisa}/download-ffs', [AdminAnalisaController::class, 'downloadPdf'])->name('analisa.download-ffs');
    Route::post('analisa/{analisa}/review', [AdminAnalisaController::class, 'review'])->name('analisa.review');

    // Daftar Reksa Dana
    Route::get('reksa-dana', [AdminReksaDanaController::class, 'index'])->name('reksa-dana.index');
    Route::post('reksa-dana/bulk-analisa', [AdminReksaDanaController::class, 'bulkAnalisa'])->name('reksa-dana.bulk-analisa');
    Route::get('reksa-dana/{reksaDana}/pdf', [AdminReksaDanaController::class, 'downloadPdf'])->name('reksa-dana.pdf');

    // Daftar Reksa Dana (master data + harga harian)
    Route::get('daftar-reksa-dana', [DaftarReksaDanaController::class, 'index'])->name('daftar-reksa-dana.index');
    Route::post('daftar-reksa-dana/upload-harga', [DaftarReksaDanaController::class, 'uploadHarga'])->name('daftar-reksa-dana.upload-harga');
    Route::post('daftar-reksa-dana/upload-harian', [DaftarReksaDanaController::class, 'uploadHarian'])->name('daftar-reksa-dana.upload-harian');
    Route::get('daftar-reksa-dana/template-harga', [DaftarReksaDanaController::class, 'downloadTemplateHarga'])->name('daftar-reksa-dana.template-harga');
    Route::get('daftar-reksa-dana/template-harian', [DaftarReksaDanaController::class, 'downloadTemplateHarian'])->name('daftar-reksa-dana.template-harian');

    Route::post('data-source-links', [DataSourceLinkController::class, 'store'])->name('data-source-links.store');
    Route::put('data-source-links/{dataSourceLink}', [DataSourceLinkController::class, 'update'])->name('data-source-links.update');
    Route::delete('data-source-links/{dataSourceLink}', [DataSourceLinkController::class, 'destroy'])->name('data-source-links.destroy');
    Route::post('data-source-links/{dataSourceLink}/upload', [DataSourceLinkController::class, 'upload'])->name('data-source-links.upload');

    // Analisa Reksa Dana (form submit, sama seperti user)
    Route::get('analisa-rd/create', [AdminAnalisaRdController::class, 'create'])->name('analisa-rd.create');
    Route::post('analisa-rd', [AdminAnalisaRdController::class, 'store'])->name('analisa-rd.store');
    Route::get('analisa-rd/template', [AdminAnalisaRdController::class, 'downloadTemplate'])->name('analisa-rd.template');
    Route::post('analisa-rd/parse-pdf', [AdminAnalisaRdController::class, 'parsePdf'])->name('analisa-rd.parse-pdf');
    Route::post('analisa-rd/parse-web-file', [AdminAnalisaRdController::class, 'parseWebFile'])->name('analisa-rd.parse-web-file');
    Route::post('analisa-rd/scrape-web-data', [AdminAnalisaRdController::class, 'scrapeWebData'])->name('analisa-rd.scrape-web-data');
    Route::post('analisa-rd/scrape-url', [AdminAnalisaRdController::class, 'scrapeUrl'])->name('analisa-rd.scrape-url');
    Route::post('analisa-rd/preview-ai', [AdminAnalisaRdController::class, 'previewAi'])->name('analisa-rd.preview-ai');
    Route::post('analisa-rd/preview-ai-plus', [AdminAnalisaRdController::class, 'previewAiPlus'])->name('analisa-rd.preview-ai-plus');

    // Daftar & Analisa Saham
    Route::get('saham', fn() => view('admin.saham.index'))->name('saham.index');
    Route::get('analisa-saham', fn() => view('admin.analisa-saham.index'))->name('analisa-saham.index');

    // Daftar & Analisa Obligasi
    Route::get('obligasi', fn() => view('admin.obligasi.index'))->name('obligasi.index');
    Route::get('analisa-obligasi', fn() => view('admin.analisa-obligasi.index'))->name('analisa-obligasi.index');
});

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.user');
    })->name('dashboard');

    // Upload & lihat hasil analisa
    Route::get('/analisa', [AnalisaController::class, 'index'])->name('analisa.index');
    Route::get('/analisa/template', [AnalisaController::class, 'downloadTemplate'])->name('analisa.template');
    Route::get('/reksa-dana', [ReksaDanaController::class, 'index'])->name('reksa-dana.index');
    Route::get('/analisa/create', [AnalisaController::class, 'create'])->name('analisa.create');
    Route::post('/analisa', [AnalisaController::class, 'store'])->name('analisa.store');
    Route::post('/analisa/parse-pdf', [AnalisaController::class, 'parsePdf'])->name('analisa.parse-pdf');
    Route::post('/analisa/parse-web-file', [AnalisaController::class, 'parseWebFile'])->name('analisa.parse-web-file');
    Route::post('/analisa/scrape-web-data', [AnalisaController::class, 'scrapeWebData'])->name('analisa.scrape-web-data');
    Route::post('/analisa/scrape-url', [AnalisaController::class, 'scrapeUrl'])->name('analisa.scrape-url');
    Route::post('/analisa/preview-ai', [AnalisaController::class, 'previewAi'])->name('analisa.preview-ai');
    Route::post('/analisa/preview-ai-plus', [AnalisaController::class, 'previewAiPlus'])->name('analisa.preview-ai-plus');
    Route::get('/analisa/{analisa}', [AnalisaController::class, 'show'])->name('analisa.show');
    Route::get('/analisa/{analisa}/pdf', [AnalisaController::class, 'exportPdf'])->name('analisa.pdf');
    Route::get('/analisa/{analisa}/download-ffs', [AnalisaController::class, 'downloadPdf'])->name('analisa.download-ffs');
    Route::delete('/analisa/{analisa}', [AnalisaController::class, 'destroy'])->name('analisa.destroy');

    Route::post('data-source-links', [UserDataSourceLinkController::class, 'store'])->name('data-source-links.store');
    Route::put('data-source-links/{dataSourceLink}', [UserDataSourceLinkController::class, 'update'])->name('data-source-links.update');
    Route::delete('data-source-links/{dataSourceLink}', [UserDataSourceLinkController::class, 'destroy'])->name('data-source-links.destroy');
    Route::post('data-source-links/{dataSourceLink}/upload', [UserDataSourceLinkController::class, 'upload'])->name('data-source-links.upload');

    // Daftar & Analisa Saham
    Route::get('/saham', fn() => view('saham.index'))->name('saham.index');
    Route::get('/analisa-saham', fn() => view('analisa-saham.index'))->name('analisa-saham.index');

    // Daftar & Analisa Obligasi
    Route::get('/obligasi', fn() => view('obligasi.index'))->name('obligasi.index');
    Route::get('/analisa-obligasi', fn() => view('analisa-obligasi.index'))->name('analisa-obligasi.index');
});

Route::middleware(['auth', 'verified'])->prefix('quiz')->name('quiz.')->group(function () {
    Route::get('/', [QuizController::class, 'index'])->name('index');
    Route::post('/submit', [QuizController::class, 'submit'])->name('submit');
    Route::get('/result', [QuizController::class, 'result'])->name('result');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/member/daftar', [MemberController::class, 'create'])->name('member.create');
    Route::post('/member/daftar', [MemberController::class, 'store'])->name('member.store');
    Route::get('/member/harga-efek', [MemberController::class, 'hargaEfek'])->name('member.harga-efek');
});

require __DIR__.'/auth.php';
