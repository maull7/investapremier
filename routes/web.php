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
use App\Http\Controllers\AnalisaFfsVisionController;
use App\Http\Controllers\AnalisaLapkeuVisionController;
use App\Http\Controllers\AnalisaSahamBrokerResearchController;
use App\Http\Controllers\Admin\ReksaDanaController as AdminReksaDanaController;
use App\Http\Controllers\Admin\AnalisaRdController as AdminAnalisaRdController;
use App\Http\Controllers\Admin\DaftarReksaDanaController;
use App\Http\Controllers\Admin\DataSourceLinkController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ObligasiController as AdminObligasiController;
use App\Http\Controllers\Admin\InvestmentManagerController as AdminInvestmentManagerController;
use App\Http\Controllers\Admin\UnitLinkController as AdminUnitLinkController;
use App\Http\Controllers\Admin\UnitLinkFfsController as AdminUnitLinkFfsController;
use App\Http\Controllers\Admin\AnalisaUlController as AdminAnalisaUlController;
use App\Http\Controllers\Admin\MonitorAnalisaUlController as AdminMonitorAnalisaUlController;
use App\Http\Controllers\Admin\MonitorAnalisaSahamController as AdminMonitorAnalisaSahamController;
use App\Http\Controllers\Admin\MonitorAnalisaObligasiController as AdminMonitorAnalisaObligasiController;
use App\Http\Controllers\Admin\AnalisaSahamController as AdminAnalisaSahamController;
use App\Http\Controllers\Admin\AnalisaObligasiController as AdminAnalisaObligasiController;
use App\Http\Controllers\User\AnalisaSahamController as UserAnalisaSahamController;
use App\Http\Controllers\User\AnalisaObligasiController as UserAnalisaObligasiController;
use App\Http\Controllers\User\DataSourceLinkController as UserDataSourceLinkController;
use App\Http\Controllers\User\StockController as UserStockController;
use App\Http\Controllers\User\ObligasiController as UserObligasiController;
use App\Http\Controllers\User\InvestmentManagerController as UserInvestmentManagerController;
use App\Http\Controllers\User\UnitLinkController as UserUnitLinkController;
use App\Http\Controllers\User\AnalisaUlController as UserAnalisaUlController;
use App\Http\Controllers\User\PerencanaanInvestasiController;
use App\Http\Controllers\ReksaDanaController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;


//before login
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
    Route::delete('analisa/{analisa}', [AdminAnalisaController::class, 'destroy'])->name('analisa.destroy');

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
    Route::post('daftar-reksa-dana/harga/store', [DaftarReksaDanaController::class, 'storeHarga'])->name('daftar-reksa-dana.harga.store');
    Route::post('daftar-reksa-dana/harga/update/{reksaDana}', [DaftarReksaDanaController::class, 'updateHarga'])->name('daftar-reksa-dana.harga.update');
    Route::delete('daftar-reksa-dana/harga/destroy/{reksaDana}', [DaftarReksaDanaController::class, 'destroyHarga'])->name('daftar-reksa-dana.harga.destroy');
    Route::post('daftar-reksa-dana/harian/store', [DaftarReksaDanaController::class, 'storeHarian'])->name('daftar-reksa-dana.harian.store');
    Route::post('daftar-reksa-dana/harian/update/{hargaReksaDana}', [DaftarReksaDanaController::class, 'updateHarian'])->name('daftar-reksa-dana.harian.update');
    Route::delete('daftar-reksa-dana/harian/destroy/{hargaReksaDana}', [DaftarReksaDanaController::class, 'destroyHarian'])->name('daftar-reksa-dana.harian.destroy');

    Route::post('data-source-links', [DataSourceLinkController::class, 'store'])->name('data-source-links.store');
    Route::put('data-source-links/{dataSourceLink}', [DataSourceLinkController::class, 'update'])->name('data-source-links.update');
    Route::delete('data-source-links/{dataSourceLink}', [DataSourceLinkController::class, 'destroy'])->name('data-source-links.destroy');
    Route::post('data-source-links/{dataSourceLink}/upload', [DataSourceLinkController::class, 'upload'])->name('data-source-links.upload');

    // Analisa Reksa Dana (form submit, sama seperti user)
    Route::get('analisa-rd/create', [AdminAnalisaRdController::class, 'create'])->name('analisa-rd.create');
    Route::post('analisa-rd', [AdminAnalisaRdController::class, 'store'])->name('analisa-rd.store');
    Route::get('analisa-rd/template', [AdminAnalisaRdController::class, 'downloadTemplate'])->name('analisa-rd.template');
    Route::post('analisa-rd/parse-pdf', [AdminAnalisaRdController::class, 'parsePdf'])->name('analisa-rd.parse-pdf');
    Route::post('analisa-rd/parse-pdf-vision', [AnalisaFfsVisionController::class, 'parsePdf'])->name('analisa-rd.parse-pdf-vision');
    Route::post('analisa-rd/parse-web-file', [AdminAnalisaRdController::class, 'parseWebFile'])->name('analisa-rd.parse-web-file');
    Route::post('analisa-rd/scrape-web-data', [AdminAnalisaRdController::class, 'scrapeWebData'])->name('analisa-rd.scrape-web-data');
    Route::post('analisa-rd/scrape-url', [AdminAnalisaRdController::class, 'scrapeUrl'])->name('analisa-rd.scrape-url');
    Route::get('analisa-rd/lookup-kode', [AdminAnalisaRdController::class, 'lookupKode'])->name('analisa-rd.lookup-kode');
    Route::post('analisa-rd/preview-ai', [AdminAnalisaRdController::class, 'previewAi'])->name('analisa-rd.preview-ai');
    Route::post('analisa-rd/preview-ai-plus', [AdminAnalisaRdController::class, 'previewAiPlus'])->name('analisa-rd.preview-ai-plus');
    Route::get('analisa-rd/lookup-sektor', [AdminAnalisaRdController::class, 'lookupSektor'])->name('analisa-rd.lookup-sektor');
    Route::get('analisa-rd/lookup-ihsg', [AdminAnalisaRdController::class, 'lookupIhsg'])->name('analisa-rd.lookup-ihsg');
    Route::get('analisa-rd/lookup-return', [AdminAnalisaRdController::class, 'lookupReturn'])->name('analisa-rd.lookup-return');
    Route::get('analisa-rd/lookup-bond-return', [AdminAnalisaRdController::class, 'lookupBondReturn'])->name('analisa-rd.lookup-bond-return');
    Route::get('analisa-rd/lookup-sukuk-return', [AdminAnalisaRdController::class, 'lookupSukukReturn'])->name('analisa-rd.lookup-sukuk-return');
    Route::get('analisa-rd/lookup-bank-data', [AdminAnalisaRdController::class, 'lookupBankData'])->name('analisa-rd.lookup-bank-data');

    // Daftar & Analisa Saham
    Route::resource('saham', StockController::class)->except(['show']);
    Route::get('saham-template', [StockController::class, 'downloadTemplate'])->name('saham.template');
    Route::post('saham-import', [StockController::class, 'import'])->name('saham.import');
    Route::get('analisa-saham', [AdminMonitorAnalisaSahamController::class, 'index'])->name('analisa-saham.index');
    Route::get('analisa-saham/create', [AdminAnalisaSahamController::class, 'create'])->name('analisa-saham.create');
    Route::post('analisa-saham', [AdminAnalisaSahamController::class, 'store'])->name('analisa-saham.store');
    Route::get('analisa-saham/template', [AdminAnalisaSahamController::class, 'downloadTemplate'])->name('analisa-saham.template');
    Route::post('analisa-saham/parse-pdf', [AdminAnalisaSahamController::class, 'parsePdf'])->name('analisa-saham.parse-pdf');
    Route::post('analisa-saham/parse-pdf-vision', [AnalisaLapkeuVisionController::class, 'parseSahamPdf'])->name('analisa-saham.parse-pdf-vision');
    Route::get('analisa-saham/parse-pdf/{uuid}/status', [AdminAnalisaSahamController::class, 'parsePdfStatus'])->name('analisa-saham.parse-pdf-status');
    Route::post('analisa-saham/preview-ai', [AdminAnalisaSahamController::class, 'previewAi'])->name('analisa-saham.preview-ai');
    Route::post('analisa-saham/preview-ai-plus', [AdminAnalisaSahamController::class, 'previewAiPlus'])->name('analisa-saham.preview-ai-plus');
    Route::get('analisa-saham/{analisa}', [AdminMonitorAnalisaSahamController::class, 'show'])->name('analisa-saham.show');
    Route::get('analisa-saham/{analisa}/pdf', [AdminMonitorAnalisaSahamController::class, 'exportPdf'])->name('analisa-saham.pdf');
    Route::get('analisa-saham/{analisa}/download-lapkeu', [AdminMonitorAnalisaSahamController::class, 'downloadLapkeu'])->name('analisa-saham.download-lapkeu');
    Route::get('analisa-saham/{analisa}/ai-status', [AdminMonitorAnalisaSahamController::class, 'checkAiStatus'])->name('analisa-saham.check-ai-status');
    Route::post('analisa-saham/{analisa}/riset-broker', [AnalisaSahamBrokerResearchController::class, 'store'])->name('analisa-saham.riset-broker.store');
    Route::get('analisa-saham/{analisa}/riset-broker/{document}/view', [AnalisaSahamBrokerResearchController::class, 'view'])->name('analisa-saham.riset-broker.view');
    Route::get('analisa-saham/{analisa}/riset-broker/{document}/download', [AnalisaSahamBrokerResearchController::class, 'download'])->name('analisa-saham.riset-broker.download');
    Route::delete('analisa-saham/{analisa}/riset-broker/{document}', [AnalisaSahamBrokerResearchController::class, 'destroy'])->name('analisa-saham.riset-broker.destroy');
    Route::post('analisa-saham/{analisa}/review', [AdminMonitorAnalisaSahamController::class, 'review'])->name('analisa-saham.review');
    Route::delete('analisa-saham/{analisa}', [AdminMonitorAnalisaSahamController::class, 'destroy'])->name('analisa-saham.destroy');

    // Daftar & Analisa Obligasi
    Route::get('obligasi', [AdminObligasiController::class, 'index'])->name('obligasi.index');
    Route::get('obligasi/harga-referensi/create', [AdminObligasiController::class, 'createHargaReferensi'])->name('obligasi.create-harga-referensi');
    Route::post('obligasi/harga-referensi', [AdminObligasiController::class, 'storeHargaReferensi'])->name('obligasi.store-harga-referensi');
    Route::get('obligasi/harga-referensi/{obligasiHargaReferensi}/edit', [AdminObligasiController::class, 'editHargaReferensi'])->name('obligasi.edit-harga-referensi');
    Route::put('obligasi/harga-referensi/{obligasiHargaReferensi}', [AdminObligasiController::class, 'updateHargaReferensi'])->name('obligasi.update-harga-referensi');
    Route::delete('obligasi/harga-referensi/{obligasiHargaReferensi}', [AdminObligasiController::class, 'destroyHargaReferensi'])->name('obligasi.destroy-harga-referensi');
    Route::get('obligasi/template-harga-referensi', [AdminObligasiController::class, 'downloadTemplateHargaReferensi'])->name('obligasi.template-harga-referensi');
    Route::post('obligasi/import-harga-referensi', [AdminObligasiController::class, 'importHargaReferensi'])->name('obligasi.import-harga-referensi');
    Route::get('obligasi/bond/create', [AdminObligasiController::class, 'createBond'])->name('obligasi.create-bond');
    Route::post('obligasi/bond', [AdminObligasiController::class, 'storeBond'])->name('obligasi.store-bond');
    Route::get('obligasi/bond/{obligasiBond}/edit', [AdminObligasiController::class, 'editBond'])->name('obligasi.edit-bond');
    Route::put('obligasi/bond/{obligasiBond}', [AdminObligasiController::class, 'updateBond'])->name('obligasi.update-bond');
    Route::delete('obligasi/bond/{obligasiBond}', [AdminObligasiController::class, 'destroyBond'])->name('obligasi.destroy-bond');
    Route::get('obligasi/template-bond', [AdminObligasiController::class, 'downloadTemplateBond'])->name('obligasi.template-bond');
    Route::post('obligasi/import-bond', [AdminObligasiController::class, 'importBond'])->name('obligasi.import-bond');
    Route::get('analisa-obligasi', [AdminMonitorAnalisaObligasiController::class, 'index'])->name('analisa-obligasi.index');
    Route::get('analisa-obligasi/create', [AdminAnalisaObligasiController::class, 'create'])->name('analisa-obligasi.create');
    Route::post('analisa-obligasi', [AdminAnalisaObligasiController::class, 'store'])->name('analisa-obligasi.store');
    Route::get('analisa-obligasi/template', [AdminAnalisaObligasiController::class, 'downloadTemplate'])->name('analisa-obligasi.template');
    Route::post('analisa-obligasi/parse-pdf', [AdminAnalisaObligasiController::class, 'parsePdf'])->name('analisa-obligasi.parse-pdf');
    Route::post('analisa-obligasi/parse-pdf-vision', [AnalisaLapkeuVisionController::class, 'parseObligasiPdf'])->name('analisa-obligasi.parse-pdf-vision');
    Route::get('analisa-obligasi/parse-pdf/{uuid}/status', [AdminAnalisaObligasiController::class, 'parsePdfStatus'])->name('analisa-obligasi.parse-pdf-status');
    Route::get('analisa-obligasi/lookup-keuangan-emiten', [AdminAnalisaObligasiController::class, 'lookupKeuanganEmiten'])->name('analisa-obligasi.lookup-keuangan-emiten');
    Route::post('analisa-obligasi/preview-ai', [AdminAnalisaObligasiController::class, 'previewAi'])->name('analisa-obligasi.preview-ai');
    Route::post('analisa-obligasi/preview-ai-plus', [AdminAnalisaObligasiController::class, 'previewAiPlus'])->name('analisa-obligasi.preview-ai-plus');
    Route::get('analisa-obligasi/{analisa}', [AdminMonitorAnalisaObligasiController::class, 'show'])->name('analisa-obligasi.show');
    Route::get('analisa-obligasi/{analisa}/pdf', [AdminMonitorAnalisaObligasiController::class, 'exportPdf'])->name('analisa-obligasi.pdf');
    Route::get('analisa-obligasi/{analisa}/download-lapkeu', [AdminMonitorAnalisaObligasiController::class, 'downloadLapkeu'])->name('analisa-obligasi.download-lapkeu');
    Route::get('analisa-obligasi/{analisa}/ai-status', [AdminMonitorAnalisaObligasiController::class, 'checkAiStatus'])->name('analisa-obligasi.check-ai-status');
    Route::post('analisa-obligasi/{analisa}/review', [AdminMonitorAnalisaObligasiController::class, 'review'])->name('analisa-obligasi.review');
    Route::delete('analisa-obligasi/{analisa}', [AdminMonitorAnalisaObligasiController::class, 'destroy'])->name('analisa-obligasi.destroy');

    // Manajer Investasi
    Route::resource('investment-managers', AdminInvestmentManagerController::class)->except(['show']);
    Route::get('investment-managers-template', [AdminInvestmentManagerController::class, 'downloadTemplate'])->name('investment-managers.template');
    Route::post('investment-managers-import', [AdminInvestmentManagerController::class, 'import'])->name('investment-managers.import');
    Route::delete('investment-managers-period/{investmentManagerPeriod}', [AdminInvestmentManagerController::class, 'destroyPeriod'])->name('investment-managers.period-destroy');

    // Unit Link
    Route::get('unit-link', [AdminUnitLinkController::class, 'index'])->name('unit-link.index');
    Route::get('unit-link/create', [AdminUnitLinkController::class, 'create'])->name('unit-link.create');
    Route::post('unit-link', [AdminUnitLinkController::class, 'store'])->name('unit-link.store');
    Route::get('unit-link/{unitLink}/edit', [AdminUnitLinkController::class, 'edit'])->name('unit-link.edit');
    Route::put('unit-link/{unitLink}', [AdminUnitLinkController::class, 'update'])->name('unit-link.update');
    Route::delete('unit-link/{unitLink}', [AdminUnitLinkController::class, 'destroy'])->name('unit-link.destroy');
    Route::get('unit-link-template', [AdminUnitLinkController::class, 'downloadTemplate'])->name('unit-link.template');
    Route::post('unit-link-import', [AdminUnitLinkController::class, 'import'])->name('unit-link.import');
    Route::get('unit-link-template-harga', [AdminUnitLinkController::class, 'downloadTemplateHarga'])->name('unit-link.template-harga');
    Route::post('unit-link-import-harga', [AdminUnitLinkController::class, 'importHarga'])->name('unit-link.import-harga');
    Route::post('unit-link-harga', [AdminUnitLinkController::class, 'storeHarga'])->name('unit-link.store-harga');
    Route::put('unit-link-harga/{hargaUnitLink}', [AdminUnitLinkController::class, 'updateHarga']);
    Route::delete('unit-link-harga/{hargaUnitLink}', [AdminUnitLinkController::class, 'destroyHarga']);

    // Monitor Unit Link FFS
    Route::get('unit-link-ffs', [AdminUnitLinkFfsController::class, 'index'])->name('unit-link-ffs.index');
    Route::post('unit-link-ffs/bulk-analisa', [AdminUnitLinkFfsController::class, 'bulkAnalisa'])->name('unit-link-ffs.bulk-analisa');
    Route::get('unit-link-ffs/{analisa}/pdf', [AdminUnitLinkFfsController::class, 'downloadPdf'])->name('unit-link-ffs.pdf');

    // Analisa Unit Link (create + store + tools)
    Route::get('analisa-ul/create', [AdminAnalisaUlController::class, 'create'])->name('analisa-ul.create');
    Route::post('analisa-ul', [AdminAnalisaUlController::class, 'store'])->name('analisa-ul.store');
    Route::get('analisa-ul/template', [AdminAnalisaUlController::class, 'downloadTemplate'])->name('analisa-ul.template');
    Route::post('analisa-ul/parse-pdf', [AdminAnalisaUlController::class, 'parsePdf'])->name('analisa-ul.parse-pdf');
    Route::post('analisa-ul/parse-pdf-vision', [AnalisaFfsVisionController::class, 'parsePdf'])->name('analisa-ul.parse-pdf-vision');
    Route::post('analisa-ul/parse-web-file', [AdminAnalisaUlController::class, 'parseWebFile'])->name('analisa-ul.parse-web-file');
    Route::post('analisa-ul/scrape-web-data', [AdminAnalisaUlController::class, 'scrapeWebData'])->name('analisa-ul.scrape-web-data');
    Route::post('analisa-ul/scrape-url', [AdminAnalisaUlController::class, 'scrapeUrl'])->name('analisa-ul.scrape-url');
    Route::post('analisa-ul/preview-ai', [AdminAnalisaUlController::class, 'previewAi'])->name('analisa-ul.preview-ai');
    Route::post('analisa-ul/preview-ai-plus', [AdminAnalisaUlController::class, 'previewAiPlus'])->name('analisa-ul.preview-ai-plus');

    // Monitor Analisa Unit Link
    Route::get('unit-link-analisa', [AdminMonitorAnalisaUlController::class, 'index'])->name('unit-link-analisa.index');
    Route::get('unit-link-analisa/{analisa}', [AdminMonitorAnalisaUlController::class, 'show'])->name('unit-link-analisa.show');
    Route::get('unit-link-analisa/{analisa}/pdf', [AdminMonitorAnalisaUlController::class, 'exportPdf'])->name('unit-link-analisa.pdf');
    Route::get('unit-link-analisa/{analisa}/download-ffs', [AdminMonitorAnalisaUlController::class, 'downloadPdf'])->name('unit-link-analisa.download-ffs');
    Route::post('unit-link-analisa/{analisa}/review', [AdminMonitorAnalisaUlController::class, 'review'])->name('unit-link-analisa.review');
    Route::delete('unit-link-analisa/{analisa}', [AdminMonitorAnalisaUlController::class, 'destroy'])->name('unit-link-analisa.destroy');

    // AI Prompts
    Route::get('ai-prompts', [App\Http\Controllers\Admin\AiPromptController::class, 'index'])->name('ai-prompts.index');
    Route::get('ai-prompts/group/{group}', [App\Http\Controllers\Admin\AiPromptController::class, 'index'])->name('ai-prompts.group');
    Route::get('ai-prompts/create', [App\Http\Controllers\Admin\AiPromptController::class, 'create'])->name('ai-prompts.create');
    Route::post('ai-prompts', [App\Http\Controllers\Admin\AiPromptController::class, 'store'])->name('ai-prompts.store');
    Route::get('ai-prompts/{key}/edit', [App\Http\Controllers\Admin\AiPromptController::class, 'edit'])->name('ai-prompts.edit');
    Route::put('ai-prompts/{key}', [App\Http\Controllers\Admin\AiPromptController::class, 'update'])->name('ai-prompts.update');
    Route::put('ai-prompts/{key}/value', [App\Http\Controllers\Admin\AiPromptController::class, 'updateValue'])->name('ai-prompts.update-value');
    Route::delete('ai-prompts/{key}', [App\Http\Controllers\Admin\AiPromptController::class, 'destroy'])->name('ai-prompts.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.user');
    })->name('dashboard');

    // Upload & lihat hasil analisa
    Route::get('/analisa', [AnalisaController::class, 'index'])->name('analisa.index');
    Route::get('/analisa/template', [AnalisaController::class, 'downloadTemplate'])->name('analisa.template');
    Route::get('/reksa-dana', [ReksaDanaController::class, 'index'])->name('reksa-dana.index');
    Route::get('/reksa-dana/{reksaDana}/edit', [ReksaDanaController::class, 'edit'])->name('reksa-dana.edit');
    Route::put('/reksa-dana/{reksaDana}', [ReksaDanaController::class, 'update'])->name('reksa-dana.update');
    Route::delete('/reksa-dana/{reksaDana}', [ReksaDanaController::class, 'destroy'])->name('reksa-dana.destroy');
    Route::get('/analisa/create', [AnalisaController::class, 'create'])->name('analisa.create');
    Route::post('/analisa', [AnalisaController::class, 'store'])->name('analisa.store');
    Route::post('/analisa/parse-pdf', [AnalisaController::class, 'parsePdf'])->name('analisa.parse-pdf');
    Route::post('/analisa/parse-pdf-vision', [AnalisaFfsVisionController::class, 'parsePdf'])->name('analisa.parse-pdf-vision');
    Route::post('/analisa/parse-web-file', [AnalisaController::class, 'parseWebFile'])->name('analisa.parse-web-file');
    Route::post('/analisa/scrape-web-data', [AnalisaController::class, 'scrapeWebData'])->name('analisa.scrape-web-data');
    Route::post('/analisa/scrape-url', [AnalisaController::class, 'scrapeUrl'])->name('analisa.scrape-url');
    Route::get('/analisa/lookup-kode', [AnalisaController::class, 'lookupKode'])->name('analisa.lookup-kode');
    Route::get('/analisa/lookup-sektor', [AnalisaController::class, 'lookupSektor'])->name('analisa.lookup-sektor');
    Route::get('/analisa/lookup-ihsg', [AnalisaController::class, 'lookupIhsg'])->name('analisa.lookup-ihsg');
    Route::get('/analisa/lookup-return', [AnalisaController::class, 'lookupReturn'])->name('analisa.lookup-return');
    Route::get('/analisa/lookup-bond-return', [AnalisaController::class, 'lookupBondReturn'])->name('analisa.lookup-bond-return');
    Route::get('/analisa/lookup-sukuk-return', [AnalisaController::class, 'lookupSukukReturn'])->name('analisa.lookup-sukuk-return');
    Route::get('/analisa/lookup-bank-data', [AnalisaController::class, 'lookupBankData'])->name('analisa.lookup-bank-data');
    Route::post('/analisa/preview-ai', [AnalisaController::class, 'previewAi'])->name('analisa.preview-ai');
    Route::post('/analisa/preview-ai-plus', [AnalisaController::class, 'previewAiPlus'])->name('analisa.preview-ai-plus');
    Route::get('/analisa/{analisa}', [AnalisaController::class, 'show'])->name('analisa.show');
    Route::get('/analisa/{analisa}/edit', [AnalisaController::class, 'edit'])->name('analisa.edit');
    Route::put('/analisa/{analisa}', [AnalisaController::class, 'update'])->name('analisa.update');
    Route::get('/analisa/{analisa}/pdf', [AnalisaController::class, 'exportPdf'])->name('analisa.pdf');
    Route::get('/analisa/{analisa}/download-ffs', [AnalisaController::class, 'downloadPdf'])->name('analisa.download-ffs');
    Route::delete('/analisa/{analisa}', [AnalisaController::class, 'destroy'])->name('analisa.destroy');

    Route::post('data-source-links', [UserDataSourceLinkController::class, 'store'])->name('data-source-links.store');
    Route::put('data-source-links/{dataSourceLink}', [UserDataSourceLinkController::class, 'update'])->name('data-source-links.update');
    Route::delete('data-source-links/{dataSourceLink}', [UserDataSourceLinkController::class, 'destroy'])->name('data-source-links.destroy');
    Route::post('data-source-links/{dataSourceLink}/upload', [UserDataSourceLinkController::class, 'upload'])->name('data-source-links.upload');

    // Daftar & Analisa Saham
    Route::resource('/saham', UserStockController::class)->except(['show']);
    // Route::get('/saham-template', [UserStockController::class, 'downloadTemplate'])->name('saham.template');
    // Route::post('/saham-import', [UserStockController::class, 'import'])->name('saham.import');
    Route::get('/analisa-saham', [UserAnalisaSahamController::class, 'index'])->name('analisa-saham.index');
    Route::get('/analisa-saham/create', [UserAnalisaSahamController::class, 'create'])->name('analisa-saham.create');
    Route::post('/analisa-saham', [UserAnalisaSahamController::class, 'store'])->name('analisa-saham.store');
    Route::get('/analisa-saham/template', [UserAnalisaSahamController::class, 'downloadTemplate'])->name('analisa-saham.template');
    Route::post('/analisa-saham/parse-pdf', [UserAnalisaSahamController::class, 'parsePdf'])->name('analisa-saham.parse-pdf');
    Route::post('/analisa-saham/parse-pdf-vision', [AnalisaLapkeuVisionController::class, 'parseSahamPdf'])->name('analisa-saham.parse-pdf-vision');
    Route::get('/analisa-saham/parse-pdf/{uuid}/status', [UserAnalisaSahamController::class, 'parsePdfStatus'])->name('analisa-saham.parse-pdf-status');
    Route::post('/analisa-saham/preview-ai', [UserAnalisaSahamController::class, 'previewAi'])->name('analisa-saham.preview-ai');
    Route::post('/analisa-saham/preview-ai-plus', [UserAnalisaSahamController::class, 'previewAiPlus'])->name('analisa-saham.preview-ai-plus');
    Route::get('/analisa-saham/{analisa}', [UserAnalisaSahamController::class, 'show'])->name('analisa-saham.show');
    Route::get('/analisa-saham/{analisa}/pdf', [UserAnalisaSahamController::class, 'exportPdf'])->name('analisa-saham.pdf');
    Route::get('/analisa-saham/{analisa}/download-lapkeu', [UserAnalisaSahamController::class, 'downloadLapkeu'])->name('analisa-saham.download-lapkeu');
    Route::get('/analisa-saham/{analisa}/ai-status', [UserAnalisaSahamController::class, 'checkAiStatus'])->name('analisa-saham.check-ai-status');
    Route::post('/analisa-saham/{analisa}/riset-broker', [AnalisaSahamBrokerResearchController::class, 'store'])->name('analisa-saham.riset-broker.store');
    Route::get('/analisa-saham/{analisa}/riset-broker/{document}/view', [AnalisaSahamBrokerResearchController::class, 'view'])->name('analisa-saham.riset-broker.view');
    Route::get('/analisa-saham/{analisa}/riset-broker/{document}/download', [AnalisaSahamBrokerResearchController::class, 'download'])->name('analisa-saham.riset-broker.download');
    Route::delete('/analisa-saham/{analisa}/riset-broker/{document}', [AnalisaSahamBrokerResearchController::class, 'destroy'])->name('analisa-saham.riset-broker.destroy');
    Route::delete('/analisa-saham/{analisa}', [UserAnalisaSahamController::class, 'destroy'])->name('analisa-saham.destroy');

    // Daftar & Analisa Obligasi
    Route::get('/obligasi', [UserObligasiController::class, 'index'])->name('obligasi.index');
    Route::get('/analisa-obligasi', [UserAnalisaObligasiController::class, 'index'])->name('analisa-obligasi.index');
    Route::get('/analisa-obligasi/create', [UserAnalisaObligasiController::class, 'create'])->name('analisa-obligasi.create');
    Route::post('/analisa-obligasi', [UserAnalisaObligasiController::class, 'store'])->name('analisa-obligasi.store');
    Route::get('/analisa-obligasi/template', [UserAnalisaObligasiController::class, 'downloadTemplate'])->name('analisa-obligasi.template');
    Route::post('/analisa-obligasi/parse-pdf', [UserAnalisaObligasiController::class, 'parsePdf'])->name('analisa-obligasi.parse-pdf');
    Route::post('/analisa-obligasi/parse-pdf-vision', [AnalisaLapkeuVisionController::class, 'parseObligasiPdf'])->name('analisa-obligasi.parse-pdf-vision');
    Route::get('/analisa-obligasi/parse-pdf/{uuid}/status', [UserAnalisaObligasiController::class, 'parsePdfStatus'])->name('analisa-obligasi.parse-pdf-status');
    Route::get('/analisa-obligasi/lookup-keuangan-emiten', [UserAnalisaObligasiController::class, 'lookupKeuanganEmiten'])->name('analisa-obligasi.lookup-keuangan-emiten');
    Route::post('/analisa-obligasi/preview-ai', [UserAnalisaObligasiController::class, 'previewAi'])->name('analisa-obligasi.preview-ai');
    Route::post('/analisa-obligasi/preview-ai-plus', [UserAnalisaObligasiController::class, 'previewAiPlus'])->name('analisa-obligasi.preview-ai-plus');
    Route::get('/analisa-obligasi/{analisa}', [UserAnalisaObligasiController::class, 'show'])->name('analisa-obligasi.show');
    Route::get('/analisa-obligasi/{analisa}/pdf', [UserAnalisaObligasiController::class, 'exportPdf'])->name('analisa-obligasi.pdf');
    Route::get('/analisa-obligasi/{analisa}/download-lapkeu', [UserAnalisaObligasiController::class, 'downloadLapkeu'])->name('analisa-obligasi.download-lapkeu');
    Route::get('/analisa-obligasi/{analisa}/ai-status', [UserAnalisaObligasiController::class, 'checkAiStatus'])->name('analisa-obligasi.check-ai-status');
    Route::delete('/analisa-obligasi/{analisa}', [UserAnalisaObligasiController::class, 'destroy'])->name('analisa-obligasi.destroy');

    // Manajer Investasi
    Route::get('/investment-managers', [UserInvestmentManagerController::class, 'index'])->name('investment-managers.index');

    // Unit Link (master data)
    Route::get('/unit-link', [UserUnitLinkController::class, 'index'])->name('unit-link.index');

    // Analisa Unit Link (user)
    Route::get('/unit-link-analisa', [UserAnalisaUlController::class, 'index'])->name('unit-link-analisa.index');
    Route::get('/unit-link-analisa/create', [UserAnalisaUlController::class, 'create'])->name('unit-link-analisa.create');
    Route::post('/unit-link-analisa', [UserAnalisaUlController::class, 'store'])->name('unit-link-analisa.store');
    Route::get('/unit-link-analisa/template', [UserAnalisaUlController::class, 'downloadTemplate'])->name('unit-link-analisa.template');
    Route::post('/unit-link-analisa/parse-pdf', [UserAnalisaUlController::class, 'parsePdf'])->name('unit-link-analisa.parse-pdf');
    Route::post('/unit-link-analisa/parse-pdf-vision', [AnalisaFfsVisionController::class, 'parsePdf'])->name('unit-link-analisa.parse-pdf-vision');
    Route::post('/unit-link-analisa/parse-web-file', [UserAnalisaUlController::class, 'parseWebFile'])->name('unit-link-analisa.parse-web-file');
    Route::post('/unit-link-analisa/scrape-web-data', [UserAnalisaUlController::class, 'scrapeWebData'])->name('unit-link-analisa.scrape-web-data');
    Route::post('/unit-link-analisa/scrape-url', [UserAnalisaUlController::class, 'scrapeUrl'])->name('unit-link-analisa.scrape-url');
    Route::post('/unit-link-analisa/preview-ai', [UserAnalisaUlController::class, 'previewAi'])->name('unit-link-analisa.preview-ai');
    Route::post('/unit-link-analisa/preview-ai-plus', [UserAnalisaUlController::class, 'previewAiPlus'])->name('unit-link-analisa.preview-ai-plus');
    Route::get('/unit-link-analisa/{analisa}', [UserAnalisaUlController::class, 'show'])->name('unit-link-analisa.show');
    Route::get('/unit-link-analisa/{analisa}/edit', [UserAnalisaUlController::class, 'edit'])->name('unit-link-analisa.edit');
    Route::put('/unit-link-analisa/{analisa}', [UserAnalisaUlController::class, 'update'])->name('unit-link-analisa.update');
    Route::get('/unit-link-analisa/{analisa}/pdf', [UserAnalisaUlController::class, 'exportPdf'])->name('unit-link-analisa.pdf');
    Route::get('/unit-link-analisa/{analisa}/download-ffs', [UserAnalisaUlController::class, 'downloadPdf'])->name('unit-link-analisa.download-ffs');
    Route::delete('/unit-link-analisa/{analisa}', [UserAnalisaUlController::class, 'destroy'])->name('unit-link-analisa.destroy');

    // Perencanaan Investasi
    Route::resource('/perencanaan-investasi', PerencanaanInvestasiController::class)->except(['show']);
    Route::get('/perencanaan-investasi/{perencanaan_investasi}', [PerencanaanInvestasiController::class, 'show'])->name('perencanaan-investasi.show');
    Route::post('/perencanaan-investasi/{perencanaan_investasi}/regenerate-ai', [PerencanaanInvestasiController::class, 'regenerateAi'])->name('perencanaan-investasi.regenerate-ai');

    // Portofolio AJAX
    Route::get('/portofolio/produk', [PerencanaanInvestasiController::class, 'getProduk'])->name('portofolio.produk');
    Route::get('/portofolio/harga', [PerencanaanInvestasiController::class, 'getHarga'])->name('portofolio.harga');
    Route::get('/portofolio/grafik', [PerencanaanInvestasiController::class, 'getGrafik'])->name('portofolio.grafik');
    Route::get('/portofolio/rekomendasi', [PerencanaanInvestasiController::class, 'getRekomendasi'])->name('portofolio.rekomendasi');
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

require __DIR__ . '/auth.php';
