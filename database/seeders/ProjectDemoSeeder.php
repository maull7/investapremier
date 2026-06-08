<?php

namespace Database\Seeders;

use App\Models\AnalisaReksaDana;
use App\Models\HargaReksaDana;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Models\MutualFundAssetAllocation;
use App\Models\MutualFundManagementTeam;
use App\Models\MutualFundPortfolioComposition;
use App\Models\ObligasiBond;
use App\Models\ObligasiHargaReferensi;
use App\Models\PheiCreditSpreadMatrix;
use App\Models\RatingObligasi;
use App\Models\ReksaDana;
use App\Models\Stock;
use App\Models\StockBrokerResearch;
use App\Models\StockCorporateAction;
use App\Models\StockFinancialReport;
use App\Models\StockNews;
use App\Models\StockPrice;
use App\Models\StockProfile;
use App\Services\InvestmentPersonService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        Storage::disk('public')->makeDirectory('demo-data');

        $managerData = [
            [
                'name' => 'PT Premier Investama',
                'kode_mi' => 'MI001',
                'kode_ojk' => 'KSM-001',
                'address' => 'Sudirman Central Business District, Jakarta',
                'phone' => '(021) 555-0101',
                'email' => 'info@premierinvestama.co.id',
                'website' => 'https://premierinvestama.co.id',
                'commissioner_president' => 'Budi Santoso',
                'commissioners' => "Rina Wijaya - Komisaris Independen\nAndi Pratama - Komisaris",
                'director_president' => 'Dewi Lestari',
                'directors' => "Fajar Nugraha - Direktur Investasi\nMaya Sari - Direktur Operasional",
                'shareholders' => "PT Nusantara Capital 70%\nPublik 30%",
                'investment_committee' => "Rina Wijaya - Ketua\nFajar Nugraha - Anggota\nMaya Sari - Anggota",
                'investment_management_team' => "Fajar Nugraha - Chief Investment Officer\nNina Citra - Portfolio Manager\nRudi Hartono - Risk Manager",
            ],
            [
                'name' => 'PT Archipelago Asset Management',
                'kode_mi' => 'MI002',
                'kode_ojk' => 'KSM-002',
                'address' => 'Mega Kuningan, Jakarta',
                'phone' => '(021) 555-0202',
                'email' => 'contact@archipelago-am.id',
                'website' => 'https://archipelago-am.id',
                'commissioner_president' => 'Hendra Wibowo',
                'commissioners' => "Sari Melati - Komisaris Independen\nDedi Kurniawan - Komisaris",
                'director_president' => 'Tasya Anggraini',
                'directors' => "Kevin Lim - Direktur Investasi\nRatih Puspa - Direktur Kepatuhan",
                'shareholders' => "PT Samudra Mandiri 60%\nPublik 40%",
                'investment_committee' => "Kevin Lim - Ketua\nRatih Puspa - Anggota\nTasya Anggraini - Anggota",
                'investment_management_team' => "Kevin Lim - Fund Manager\nRatih Puspa - Compliance Lead\nAyu Laras - Analyst",
            ],
        ];

        $managers = collect($managerData)->map(function (array $data) {
            return tap(InvestmentManager::updateOrCreate(
                ['name' => $data['name']],
                $data + ['last_updated_at' => now()]
            ));
        });

        $periodSeeds = [
            ['manager' => 'PT Premier Investama', 'period_date' => '2025-12-31', 'aum' => 1550000000000, 'up' => 610000000],
            ['manager' => 'PT Premier Investama', 'period_date' => '2026-03-31', 'aum' => 1625000000000, 'up' => 642000000],
            ['manager' => 'PT Archipelago Asset Management', 'period_date' => '2025-12-31', 'aum' => 920000000000, 'up' => 402000000],
            ['manager' => 'PT Archipelago Asset Management', 'period_date' => '2026-03-31', 'aum' => 980000000000, 'up' => 430000000],
        ];

        foreach ($periodSeeds as $row) {
            $manager = $managers->firstWhere('name', $row['manager']);
            if (!$manager) {
                continue;
            }

            InvestmentManagerPeriod::updateOrCreate(
                ['investment_manager_id' => $manager->id, 'period_date' => $row['period_date']],
                [
                    'aum' => $row['aum'],
                    'up' => $row['up'],
                    'mata_uang' => 'IDR',
                    'tahun' => Carbon::parse($row['period_date'])->year,
                    'kuartal' => (int) ceil(Carbon::parse($row['period_date'])->month / 3),
                ]
            );
        }

        $fundSeeds = [
            [
                'kode_reksa_dana' => 'RD001',
                'nama_reksa_dana' => 'Premier Equity Growth Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'investment_manager_name' => 'PT Premier Investama',
                'jenis' => 'Saham',
                'kategori' => ['Saham'],
                'kategori_produk' => 'saham',
                'kelas' => 'A',
                'benchmark' => 'JCI',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 2500.000000,
                'tanggal_nab' => '2026-06-06',
                'risk_category' => 'Tinggi',
                'description' => 'Reksa dana saham aktif dengan fokus sektor perbankan dan teknologi.',
            ],
            [
                'kode_reksa_dana' => 'RD002',
                'nama_reksa_dana' => 'Premier Fixed Income Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'investment_manager_name' => 'PT Premier Investama',
                'jenis' => 'Pendapatan Tetap',
                'kategori' => ['Pendapatan Tetap'],
                'kategori_produk' => 'pendapatan_tetap',
                'kelas' => 'A',
                'benchmark' => 'Indeks Obligasi Pemerintah',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1500.000000,
                'tanggal_nab' => '2026-06-06',
                'risk_category' => 'Sedang',
                'description' => 'Produk pendapatan tetap dengan eksposur obligasi pemerintah dan korporasi.',
            ],
            [
                'kode_reksa_dana' => 'RD003',
                'nama_reksa_dana' => 'Premier Balanced Fund',
                'nama_manajer_investasi' => 'PT Archipelago Asset Management',
                'investment_manager_name' => 'PT Archipelago Asset Management',
                'jenis' => 'Campuran',
                'kategori' => ['Campuran'],
                'kategori_produk' => 'campuran',
                'kelas' => 'A',
                'benchmark' => '50% JCI + 50% Obligasi',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1800.000000,
                'tanggal_nab' => '2026-06-06',
                'risk_category' => 'Sedang',
                'description' => 'Reksa dana campuran untuk profil risiko moderat.',
            ],
            [
                'kode_reksa_dana' => 'RD004',
                'nama_reksa_dana' => 'Premier Money Market Fund',
                'nama_manajer_investasi' => 'PT Archipelago Asset Management',
                'investment_manager_name' => 'PT Archipelago Asset Management',
                'jenis' => 'Pasar Uang',
                'kategori' => ['Pasar Uang'],
                'kategori_produk' => 'pasar_uang',
                'kelas' => 'A',
                'benchmark' => 'Indeks Pasar Uang',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1100.000000,
                'tanggal_nab' => '2026-06-06',
                'risk_category' => 'Rendah',
                'description' => 'Reksa dana pasar uang dengan likuiditas tinggi.',
            ],
        ];

        $fundModels = [];
        foreach ($fundSeeds as $seed) {
            $manager = $managers->firstWhere('name', $seed['investment_manager_name']);
            if (!$manager) {
                continue;
            }

            $fund = ReksaDana::updateOrCreate(
                ['kode_reksa_dana' => $seed['kode_reksa_dana']],
                [
                    'investment_manager_id' => $manager->id,
                    'nama_reksa_dana' => $seed['nama_reksa_dana'],
                    'nama_manajer_investasi' => $seed['nama_manajer_investasi'],
                    'jenis' => $seed['jenis'],
                    'kategori' => $seed['kategori'],
                    'kategori_produk' => $seed['kategori_produk'],
                    'kelas' => $seed['kelas'],
                    'benchmark' => $seed['benchmark'],
                    'mata_uang' => $seed['mata_uang'],
                    'nab_per_unit' => $seed['nab_per_unit'],
                    'tanggal_nab' => $seed['tanggal_nab'],
                    'risk_category' => $seed['risk_category'],
                    'description' => $seed['description'],
                ]
            );

            $fundModels[] = $fund;

            $teams = [
                ['type' => 'committee', 'name' => $manager->commissioner_president, 'position' => 'Ketua Komite Investasi'],
                ['type' => 'committee', 'name' => trim(explode("\n", $manager->investment_committee)[1] ?? 'Rina Wijaya'), 'position' => 'Anggota Komite Investasi'],
                ['type' => 'investment_manager', 'name' => trim(explode("\n", $manager->investment_management_team)[0] ?? 'Fajar Nugraha'), 'position' => 'Portfolio Manager'],
                ['type' => 'investment_manager', 'name' => trim(explode("\n", $manager->investment_management_team)[1] ?? 'Nina Citra'), 'position' => 'Assistant Portfolio Manager'],
            ];

            foreach ($teams as $team) {
                if ($team['name'] === '') {
                    continue;
                }

                MutualFundManagementTeam::updateOrCreate(
                    [
                        'reksa_dana_id' => $fund->id,
                        'type' => $team['type'],
                        'name' => $team['name'],
                        'position' => $team['position'],
                    ],
                    []
                );
            }
        }

        $fundHistory = [
            'RD001' => [
                ['2026-01-31', 2380.120000, 1400000000000, 588000000],
                ['2026-02-28', 2432.450000, 1460000000000, 600000000],
                ['2026-03-31', 2468.900000, 1512000000000, 612000000],
                ['2026-04-30', 2499.500000, 1540000000000, 620000000],
                ['2026-05-31', 2521.300000, 1580000000000, 630000000],
                ['2026-06-06', 2500.000000, 1600000000000, 640000000],
            ],
            'RD002' => [
                ['2026-01-31', 1450.220000, 1880000000000, 1280000000],
                ['2026-02-28', 1461.100000, 1905000000000, 1295000000],
                ['2026-03-31', 1478.820000, 1942000000000, 1309000000],
                ['2026-04-30', 1491.120000, 1971000000000, 1315000000],
                ['2026-05-31', 1498.500000, 2003000000000, 1324000000],
                ['2026-06-06', 1500.000000, 2015000000000, 1330000000],
            ],
            'RD003' => [
                ['2026-01-31', 1710.330000, 760000000000, 421000000],
                ['2026-02-28', 1734.510000, 776000000000, 426000000],
                ['2026-03-31', 1761.080000, 790000000000, 431000000],
                ['2026-04-30', 1780.100000, 802000000000, 438000000],
                ['2026-05-31', 1791.920000, 815000000000, 442000000],
                ['2026-06-06', 1800.000000, 820000000000, 444444444],
            ],
            'RD004' => [
                ['2026-01-31', 1092.220000, 520000000000, 475000000],
                ['2026-02-28', 1094.800000, 525000000000, 476500000],
                ['2026-03-31', 1097.120000, 530000000000, 478000000],
                ['2026-04-30', 1098.900000, 535000000000, 479500000],
                ['2026-05-31', 1099.750000, 540000000000, 481000000],
                ['2026-06-06', 1100.000000, 542000000000, 482000000],
            ],
        ];

        foreach ($fundModels as $fund) {
            foreach ($fundHistory[$fund->kode_reksa_dana] ?? [] as [$date, $nav, $aum, $up]) {
                HargaReksaDana::updateOrCreate(
                    ['reksa_dana_id' => $fund->id, 'tanggal' => $date],
                    ['nab_per_unit' => $nav, 'aum' => $aum, 'unit_participation' => $up]
                );
            }
        }

        $assetAllocations = [
            'RD001' => ['2026-06-01', 78.5, 12.0, 5.0, 4.5],
            'RD002' => ['2026-06-01', 8.0, 74.0, 10.0, 8.0],
            'RD003' => ['2026-06-01', 48.0, 32.0, 10.0, 10.0],
            'RD004' => ['2026-06-01', 2.0, 3.0, 88.0, 7.0],
        ];

        foreach ($fundModels as $fund) {
            $allocation = $assetAllocations[$fund->kode_reksa_dana] ?? null;
            if (!$allocation) {
                continue;
            }

            MutualFundAssetAllocation::updateOrCreate(
                ['reksa_dana_id' => $fund->id, 'period_date' => $allocation[0]],
                [
                    'equity_percent' => $allocation[1],
                    'bond_percent' => $allocation[2],
                    'money_market_percent' => $allocation[3],
                    'cash_percent' => $allocation[4],
                ]
            );
        }

        $portfolioSeeds = [
            'RD001' => [
                ['2026-06-01', 'BBCA', 'Saham', 9.10],
                ['2026-06-01', 'BBRI', 'Saham', 8.00],
                ['2026-06-01', 'TLKM', 'Saham', 6.20],
                ['2026-06-01', 'ADRO', 'Saham', 4.70],
            ],
            'RD002' => [
                ['2026-06-01', 'FR0090', 'Obligasi Pemerintah', 12.50],
                ['2026-06-01', 'FR0085', 'Obligasi Pemerintah', 10.80],
                ['2026-06-01', 'SUKUK001', 'Sukuk', 6.20],
            ],
            'RD003' => [
                ['2026-06-01', 'BBCA', 'Saham', 6.40],
                ['2026-06-01', 'FR0090', 'Obligasi', 7.30],
                ['2026-06-01', 'TLKM', 'Saham', 4.90],
            ],
            'RD004' => [
                ['2026-06-01', 'SRBI', 'Pasar Uang', 18.00],
                ['2026-06-01', 'Deposito A', 'Kas', 12.00],
            ],
        ];

        foreach ($fundModels as $fund) {
            foreach ($portfolioSeeds[$fund->kode_reksa_dana] ?? [] as [$date, $name, $type, $weight]) {
                MutualFundPortfolioComposition::updateOrCreate(
                    [
                        'reksa_dana_id' => $fund->id,
                        'period_date' => $date,
                        'security_name' => $name,
                    ],
                    [
                        'security_type' => $type,
                        'weight_percent' => $weight,
                    ]
                );
            }
        }

        app(InvestmentPersonService::class)->syncInvestmentManager($managers->firstWhere('name', 'PT Premier Investama'));
        app(InvestmentPersonService::class)->syncInvestmentManager($managers->firstWhere('name', 'PT Archipelago Asset Management'));
        foreach ($fundModels as $fund) {
            app(InvestmentPersonService::class)->syncFund($fund);
        }

        $stocks = [
            ['kode' => 'BBCA', 'nama' => 'Bank Central Asia Tbk', 'sektor' => 'Keuangan', 'sub_industri' => 'Bank', 'harga_terbaru' => 9800, 'harga_penutupan_sebelumnya' => 9700, 'harga_pembukaan' => 9750, 'harga_tertinggi' => 9850, 'harga_terendah' => 9680, 'volume' => 125000000, 'value' => 1220000000000, 'frekuensi' => 45000, 'jumlah_saham' => 123000000000, 'market_capital' => 1205400000000000, 'last_update' => '2026-06-06'],
            ['kode' => 'BBRI', 'nama' => 'Bank Rakyat Indonesia Tbk', 'sektor' => 'Keuangan', 'sub_industri' => 'Bank', 'harga_terbaru' => 5200, 'harga_penutupan_sebelumnya' => 5150, 'harga_pembukaan' => 5160, 'harga_tertinggi' => 5250, 'harga_terendah' => 5140, 'volume' => 142000000, 'value' => 738400000000, 'frekuensi' => 38000, 'jumlah_saham' => 151000000000, 'market_capital' => 784000000000000, 'last_update' => '2026-06-06'],
            ['kode' => 'TLKM', 'nama' => 'Telkom Indonesia Tbk', 'sektor' => 'Infrastruktur', 'sub_industri' => 'Telekomunikasi', 'harga_terbaru' => 3050, 'harga_penutupan_sebelumnya' => 3020, 'harga_pembukaan' => 3030, 'harga_tertinggi' => 3070, 'harga_terendah' => 3010, 'volume' => 98000000, 'value' => 298900000000, 'frekuensi' => 25000, 'jumlah_saham' => 99000000000, 'market_capital' => 302000000000000, 'last_update' => '2026-06-06'],
            ['kode' => 'ASII', 'nama' => 'Astra International Tbk', 'sektor' => 'Industri', 'sub_industri' => 'Otomotif', 'harga_terbaru' => 5400, 'harga_penutupan_sebelumnya' => 5350, 'harga_pembukaan' => 5360, 'harga_tertinggi' => 5425, 'harga_terendah' => 5330, 'volume' => 61000000, 'value' => 330000000000, 'frekuensi' => 18000, 'jumlah_saham' => 40400000000, 'market_capital' => 218160000000000, 'last_update' => '2026-06-06'],
            ['kode' => 'ADRO', 'nama' => 'Adaro Energy Indonesia Tbk', 'sektor' => 'Energi', 'sub_industri' => 'Batubara', 'harga_terbaru' => 2300, 'harga_penutupan_sebelumnya' => 2250, 'harga_pembukaan' => 2260, 'harga_tertinggi' => 2325, 'harga_terendah' => 2240, 'volume' => 85000000, 'value' => 195500000000, 'frekuensi' => 16000, 'jumlah_saham' => 31800000000, 'market_capital' => 73140000000000, 'last_update' => '2026-06-06'],
            ['kode' => 'UNVR', 'nama' => 'Unilever Indonesia Tbk', 'sektor' => 'Konsumsi', 'sub_industri' => 'Barang Konsumsi', 'harga_terbaru' => 3200, 'harga_penutupan_sebelumnya' => 3180, 'harga_pembukaan' => 3190, 'harga_tertinggi' => 3230, 'harga_terendah' => 3170, 'volume' => 42000000, 'value' => 134400000000, 'frekuensi' => 12000, 'jumlah_saham' => 38100000000, 'market_capital' => 121920000000000, 'last_update' => '2026-06-06'],
        ];

        foreach ($stocks as $row) {
            $stock = Stock::updateOrCreate(
                ['kode' => $row['kode']],
                $row
            );

            StockProfile::updateOrCreate(
                ['stock_code' => $stock->kode],
                [
                    'stock_id' => $stock->id,
                    'company_name' => $stock->nama,
                    'sector' => $stock->sektor,
                    'sub_sector' => $stock->sub_industri,
                    'industry' => $stock->sub_industri,
                    'website' => 'https://www.idx.co.id',
                    'email' => strtolower($stock->kode) . '@example.com',
                    'phone' => '(021) 555-1000',
                    'address' => $stock->nama . ', Jakarta',
                    'description' => 'Profil dummy untuk melihat tampilan detail saham.',
                ]
            );

            $basePrice = (float) $stock->harga_terbaru;
            $days = 5;
            for ($i = 0; $i < $days; $i++) {
                $date = Carbon::parse('2026-06-06')->subDays($days - $i - 1)->toDateString();
                $open = $basePrice * (0.985 + ($i * 0.004));
                $close = $basePrice * (0.988 + ($i * 0.005));

                StockPrice::updateOrCreate(
                    ['kode_efek' => $stock->kode, 'tanggal' => $date, 'sumber' => 'dummy'],
                    [
                        'stock_id' => $stock->id,
                        'nama_efek' => $stock->nama,
                        'jenis' => 'Saham',
                        'harga' => $close,
                        'open' => $open,
                        'high' => max($open, $close) * 1.01,
                        'low' => min($open, $close) * 0.99,
                        'close' => $close,
                        'volume' => rand(9000000, 16000000),
                    ]
                );
            }

            StockCorporateAction::updateOrCreate(
                ['stock_id' => $stock->id, 'action_date' => '2026-05-15', 'action_type' => 'Dividen'],
                [
                    'description' => 'Dividen tunai interim untuk demo data.',
                    'value' => 120.50,
                ]
            );

            StockFinancialReport::updateOrCreate(
                ['stock_id' => $stock->id, 'report_year' => 2025, 'report_period' => 'FY'],
                [
                    'total_asset' => $stock->market_capital * 0.55,
                    'total_liabilities' => $stock->market_capital * 0.25,
                    'total_equity' => $stock->market_capital * 0.30,
                    'revenue' => $stock->market_capital * 0.18,
                    'operating_income' => $stock->market_capital * 0.08,
                    'net_income' => $stock->market_capital * 0.06,
                    'cfo' => $stock->market_capital * 0.07,
                    'cfi' => -($stock->market_capital * 0.03),
                    'cff' => $stock->market_capital * 0.01,
                ]
            );

            StockBrokerResearch::updateOrCreate(
                ['stock_id' => $stock->id, 'broker_name' => 'Premier Securities', 'research_date' => '2026-06-01'],
                [
                    'rating' => 'BUY',
                    'target_price' => $basePrice * 1.12,
                    'pdf_file' => 'demo-data/broker-' . Str::lower($stock->kode) . '.pdf',
                    'ai_summary' => 'Riset broker dummy untuk melihat hasil detail saham.',
                ]
            );

            StockNews::updateOrCreate(
                ['stock_id' => $stock->id, 'title' => $stock->kode . ' cetak kinerja positif'],
                [
                    'source' => 'Demo News',
                    'url' => 'https://example.com/news/' . Str::lower($stock->kode),
                    'published_at' => '2026-06-06 09:00:00',
                    'summary' => 'Berita dummy untuk melihat halaman detail saham.',
                ]
            );
        }

        $ratings = collect([
            ['kode' => 'AAA', 'nama' => 'AAA', 'urutan' => 1],
            ['kode' => 'AA', 'nama' => 'AA', 'urutan' => 2],
            ['kode' => 'A', 'nama' => 'A', 'urutan' => 3],
            ['kode' => 'BBB', 'nama' => 'BBB', 'urutan' => 4],
        ])->map(fn ($row) => RatingObligasi::updateOrCreate(['kode' => $row['kode']], $row));

        foreach ([12, 24, 36, 60, 120] as $tenor) {
            PheiCreditSpreadMatrix::updateOrCreate(
                ['data_date' => '2026-06-06', 'tenor_bulan' => $tenor, 'source' => 'PHEI'],
                [
                    'rating_aaa' => 0.35 + ($tenor / 1200),
                    'rating_aa' => 0.65 + ($tenor / 1000),
                    'rating_a' => 1.05 + ($tenor / 800),
                    'rating_bbb' => 1.55 + ($tenor / 650),
                ]
            );
        }

        $bondReference = [
            ['kode' => 'FR0090', 'nama' => 'Obligasi Negara FR0090', 'emiten' => 'Pemerintah RI', 'rating' => 'AAA', 'kupon' => 5.20, 'harga_persen' => 102.40, 'ytm' => 5.12, 'current_yield' => 5.09, 'outstanding_amount' => 12500000000000],
            ['kode' => 'FR0085', 'nama' => 'Obligasi Negara FR0085', 'emiten' => 'Pemerintah RI', 'rating' => 'AAA', 'kupon' => 5.50, 'harga_persen' => 103.20, 'ytm' => 5.28, 'current_yield' => 5.24, 'outstanding_amount' => 9800000000000],
            ['kode' => 'OBLIGASI001', 'nama' => 'Obligasi Premier Finance 2029', 'emiten' => 'Premier Finance', 'rating' => 'A', 'kupon' => 8.25, 'harga_persen' => 99.80, 'ytm' => 8.45, 'current_yield' => 8.30, 'outstanding_amount' => 2500000000000],
            ['kode' => 'OBLIGASI002', 'nama' => 'Obligasi Nusantara 2031', 'emiten' => 'Nusantara Industries', 'rating' => 'AA', 'kupon' => 7.10, 'harga_persen' => 101.10, 'ytm' => 6.95, 'current_yield' => 7.02, 'outstanding_amount' => 1800000000000],
        ];

        foreach ($bondReference as $row) {
            ObligasiHargaReferensi::updateOrCreate(
                ['kode' => $row['kode']],
                $row + [
                    'tanggal_terbit' => '2022-01-01',
                    'jatuh_tempo' => '2029-12-31',
                    'denominasi' => 'IDR',
                    'total_val' => $row['outstanding_amount'] * 0.92,
                    'syariah' => false,
                ]
            );
        }

        $bondStatements = [
            ['kode' => 'OBLIGASI001', 'periode' => '2025-12', 'total_asset' => 8500000000000, 'total_liabilities' => 4200000000000, 'net_revenue' => 1200000000000, 'net_income' => 250000000000, 'cash_flows_operating_activities' => 480000000000],
            ['kode' => 'OBLIGASI002', 'periode' => '2025-12', 'total_asset' => 7600000000000, 'total_liabilities' => 3900000000000, 'net_revenue' => 980000000000, 'net_income' => 210000000000, 'cash_flows_operating_activities' => 410000000000],
        ];

        foreach ($bondStatements as $row) {
            ObligasiBond::updateOrCreate(
                ['kode' => $row['kode'], 'periode' => $row['periode']],
                $row + [
                    'current_asset' => 0,
                    'current_liabilities' => 0,
                    'retained_earning' => 0,
                    'equity' => 0,
                    'interest_expense' => 0,
                    'laba_operasional' => 0,
                    'cash_equivalents' => 0,
                    'account_receivable' => 0,
                    'inventories' => 0,
                    'other_current_asset' => 0,
                    'fixed_asset' => 0,
                    'other_non_current_asset' => 0,
                    'account_payable' => 0,
                    'accruals' => 0,
                    'short_term_loans' => 0,
                    'current_maturities_of_long_term_loans' => 0,
                    'other_current_liabilities' => 0,
                    'long_term_loans' => 0,
                    'employee_benefits' => 0,
                    'other_non_current_liabilities' => 0,
                    'total_non_current_liabilities' => 0,
                    'share_capital' => 0,
                    'additional_paid_in_capital' => 0,
                    'others' => 0,
                    'non_controlling_interest' => 0,
                    'total_equity_equity_to_parent_entity' => 0,
                    'cost_of_good_sold' => 0,
                    'gross_income' => 0,
                    'operational_expense' => 0,
                    'other_income_expense' => 0,
                    'income_before_tax' => 0,
                    'taxes' => 0,
                    'ebit' => 0,
                    'ebitda' => 0,
                    'net_income_attributable_to_non_controlling_interest' => 0,
                    'cash_flows_investment' => 0,
                    'cash_flows_financing' => 0,
                ]
            );
        }

        $analysisUserId = 1;
        AnalisaReksaDana::updateOrCreate(
            ['kode_reksa_dana' => 'RD001', 'user_id' => $analysisUserId, 'product_type' => 'reksa_dana'],
            [
                'product_type' => 'reksa_dana',
                'nama_reksa_dana' => 'Premier Equity Growth Fund',
                'jenis_reksa_dana' => 'Saham',
                'kategori' => ['Saham'],
                'manajer_investasi' => 'PT Premier Investama',
                'bank_kustodian' => 'Bank Mandiri',
                'mata_uang' => 'IDR',
                'total_aum' => 1600000000000,
                'unit_penyertaan' => 640000000,
                'nab_per_unit' => 2500,
                'ffs_bulan' => 6,
                'ffs_tahun' => 2026,
                'jenis_laporan' => 'laporan_tahunan',
                'tahun_laporan' => 2026,
                'tanggal_data' => '2026-06-06',
                'mode' => 'manual',
                'status' => 'submitted',
            ]
        );
    }
}
