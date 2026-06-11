<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── reksa_dana ──────────────────────────────────────────────────────

        Schema::table('reksa_dana', function (Blueprint $table) {
            if (!Schema::hasColumn('reksa_dana', 'pasardana_id')) {
                $table->integer('pasardana_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('reksa_dana', 'isin_code')) {
                $table->string('isin_code', 30)->nullable()->after('nama_reksa_dana');
            }
            if (!Schema::hasColumn('reksa_dana', 'is_etf')) {
                $table->boolean('is_etf')->nullable()->after('kategori_produk');
            }
            if (!Schema::hasColumn('reksa_dana', 'is_index')) {
                $table->boolean('is_index')->nullable()->after('is_etf');
            }
            if (!Schema::hasColumn('reksa_dana', 'conservative_category')) {
                $table->string('conservative_category', 50)->nullable()->after('is_index');
            }
            if (!Schema::hasColumn('reksa_dana', 'dividend')) {
                $table->boolean('dividend')->nullable()->after('conservative_category');
            }
        });

        // Return columns (12 periodik)
        $returnCols = [
            'return_1d', 'return_1w', 'return_mtd', 'return_1m', 'return_3m',
            'return_6m', 'return_ytd', 'return_1y', 'return_3y', 'return_5y',
            'return_10y', 'return_inception',
        ];
        Schema::table('reksa_dana', function (Blueprint $table) use ($returnCols) {
            foreach ($returnCols as $col) {
                if (!Schema::hasColumn('reksa_dana', $col)) {
                    $table->decimal($col, 10, 6)->nullable()->after('tanggal_nab');
                }
            }
        });

        // Annualized return
        $annualCols = ['annualized_return_1y', 'annualized_return_3y', 'annualized_return_5y', 'annualized_return_10y'];
        Schema::table('reksa_dana', function (Blueprint $table) use ($annualCols) {
            foreach ($annualCols as $col) {
                if (!Schema::hasColumn('reksa_dana', $col)) {
                    $table->decimal($col, 10, 6)->nullable()->after('return_inception');
                }
            }
        });

        // Risk metrics — each metric has 4 period variants (1y, 3y, 5y, 10y)
        $metricGroups = [
            ['prefix' => 'stdev', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'beta', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'sharpe_ratio', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'sortino_ratio', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'treynor_ratio', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'jensen_alpha', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'tracking_error', 'periods' => ['1y', '3y', '5y', '10y']],
            ['prefix' => 'max_drawdown', 'periods' => ['1y', '3y', '5y', '10y']],
        ];

        // Build ordered list of all metric columns
        $allMetricCols = [];
        foreach ($metricGroups as $group) {
            foreach ($group['periods'] as $p) {
                $allMetricCols[] = $group['prefix'] . '_' . $p;
            }
        }
        Schema::table('reksa_dana', function (Blueprint $table) use ($allMetricCols) {
            foreach ($allMetricCols as $col) {
                if (!Schema::hasColumn('reksa_dana', $col)) {
                    $table->decimal($col, 10, 6)->nullable()->after('annualized_return_10y');
                }
            }
        });

        // AUM, unit, fee, rating
        Schema::table('reksa_dana', function (Blueprint $table) {
            $ratingCols = ['yearly_rating', 'one_year_rating', 'three_year_rating', 'five_year_rating', 'ten_year_rating'];
            foreach ($ratingCols as $col) {
                if (!Schema::hasColumn('reksa_dana', $col)) {
                    $table->tinyInteger($col)->nullable()->after('investment_manager_fee');
                }
            }
        });

        Schema::table('reksa_dana', function (Blueprint $table) {
            if (!Schema::hasColumn('reksa_dana', 'aum')) {
                $table->decimal('aum', 24, 2)->nullable()->after('max_drawdown_10y');
            }
            if (!Schema::hasColumn('reksa_dana', 'total_unit')) {
                $table->decimal('total_unit', 24, 2)->nullable()->after('aum');
            }
            if (!Schema::hasColumn('reksa_dana', 'expense_ratio')) {
                $table->decimal('expense_ratio', 10, 6)->nullable()->after('total_unit');
            }
            if (!Schema::hasColumn('reksa_dana', 'investment_manager_fee')) {
                $table->string('investment_manager_fee', 100)->nullable()->after('expense_ratio');
            }
        });

        // Date fields
        $dateCols = [
            'aum_published_date', 'aum_last_update', 'last_update',
            'last_fund_factsheet', 'last_updated_portfolio', 'expense_ratio_date',
        ];
        Schema::table('reksa_dana', function (Blueprint $table) use ($dateCols) {
            foreach ($dateCols as $col) {
                if (!Schema::hasColumn('reksa_dana', $col)) {
                    $table->date($col)->nullable()->after('ten_year_rating');
                }
            }
        });

        // ── investment_managers ──────────────────────────────────────────────

        Schema::table('investment_managers', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_managers', 'pasardana_id')) {
                $table->integer('pasardana_id')->nullable()->unique()->after('kode_ojk');
            }
            if (!Schema::hasColumn('investment_managers', 'fax')) {
                $table->string('fax', 50)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('investment_managers', 'modal_dasar')) {
                $table->decimal('modal_dasar', 20, 2)->nullable()->after('website');
            }
            if (!Schema::hasColumn('investment_managers', 'modal_disetor')) {
                $table->decimal('modal_disetor', 20, 2)->nullable()->after('modal_dasar');
            }
            if (!Schema::hasColumn('investment_managers', 'izin_mi')) {
                $table->string('izin_mi', 200)->nullable()->after('modal_disetor');
            }
            if (!Schema::hasColumn('investment_managers', 'izin_ppe')) {
                $table->string('izin_ppe', 200)->nullable()->after('izin_mi');
            }
            if (!Schema::hasColumn('investment_managers', 'izin_pee')) {
                $table->string('izin_pee', 200)->nullable()->after('izin_ppe');
            }
        });
    }

    public function down(): void
    {
        // ── reksa_dana ──────────────────────────────────────────────────────

        $allCols = [
            // identity
            'pasardana_id', 'isin_code',
            // flags
            'is_etf', 'is_index', 'conservative_category', 'dividend',
            // returns
            'return_1d', 'return_1w', 'return_mtd', 'return_1m', 'return_3m',
            'return_6m', 'return_ytd', 'return_1y', 'return_3y', 'return_5y',
            'return_10y', 'return_inception',
            // annualized
            'annualized_return_1y', 'annualized_return_3y', 'annualized_return_5y',
            'annualized_return_10y',
            // risk metrics
            'stdev_1y', 'stdev_3y', 'stdev_5y', 'stdev_10y',
            'beta_1y', 'beta_3y', 'beta_5y', 'beta_10y',
            'sharpe_ratio_1y', 'sharpe_ratio_3y', 'sharpe_ratio_5y', 'sharpe_ratio_10y',
            'sortino_ratio_1y', 'sortino_ratio_3y', 'sortino_ratio_5y', 'sortino_ratio_10y',
            'treynor_ratio_1y', 'treynor_ratio_3y', 'treynor_ratio_5y', 'treynor_ratio_10y',
            'jensen_alpha_1y', 'jensen_alpha_3y', 'jensen_alpha_5y', 'jensen_alpha_10y',
            'tracking_error_1y', 'tracking_error_3y', 'tracking_error_5y', 'tracking_error_10y',
            'max_drawdown_1y', 'max_drawdown_3y', 'max_drawdown_5y', 'max_drawdown_10y',
            // financial
            'aum', 'total_unit', 'expense_ratio', 'investment_manager_fee',
            // ratings
            'yearly_rating', 'one_year_rating', 'three_year_rating', 'five_year_rating',
            'ten_year_rating',
            // dates
            'aum_published_date', 'aum_last_update', 'last_update',
            'last_fund_factsheet', 'last_updated_portfolio', 'expense_ratio_date',
        ];

        Schema::table('reksa_dana', function (Blueprint $table) use ($allCols) {
            $existing = array_filter($allCols, fn($c) => Schema::hasColumn('reksa_dana', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        // ── investment_managers ──────────────────────────────────────────────

        $miCols = ['pasardana_id', 'fax', 'modal_dasar', 'modal_disetor', 'izin_mi', 'izin_ppe', 'izin_pee'];
        Schema::table('investment_managers', function (Blueprint $table) use ($miCols) {
            $existing = array_filter($miCols, fn($c) => Schema::hasColumn('investment_managers', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
