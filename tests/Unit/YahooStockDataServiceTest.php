<?php

namespace Tests\Unit;

use App\Models\Stock;
use App\Services\YahooStockDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YahooStockDataServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.yfapi.key' => 'test-key',
            'services.yfapi.url' => 'https://yfapi.test',
            'services.yahoo_finance.search_url' => 'https://yahoo.test/v1/finance/search',
        ]);
        Cache::flush();
    }

    public function test_it_maps_summary_financial_statements_analysts_and_news(): void
    {
        Http::fake([
            'https://yfapi.test/v11/finance/quoteSummary/*' => Http::response([
                'quoteSummary' => [
                    'result' => [[
                        'assetProfile' => [
                            'industry' => 'Banks - Regional',
                            'sector' => 'Financial Services',
                            'website' => 'https://www.bca.co.id',
                            'longBusinessSummary' => 'Bank profile.',
                        ],
                        'summaryDetail' => [
                            'trailingPE' => ['raw' => 21.5],
                            'dividendYield' => ['raw' => 0.025],
                        ],
                        'defaultKeyStatistics' => [
                            'priceToBook' => ['raw' => 4.2],
                            'trailingEps' => ['raw' => 410],
                        ],
                        'financialData' => [
                            'profitMargins' => ['raw' => 0.4],
                            'targetMeanPrice' => ['raw' => 10500],
                            'recommendationKey' => 'buy',
                            'numberOfAnalystOpinions' => ['raw' => 8],
                        ],
                        'incomeStatementHistory' => [
                            'incomeStatementHistory' => [[
                                'endDate' => ['fmt' => '2025-12-31'],
                                'totalRevenue' => ['raw' => 1000],
                                'netIncome' => ['raw' => 400],
                            ]],
                        ],
                        'balanceSheetHistory' => [
                            'balanceSheetStatements' => [[
                                'endDate' => ['fmt' => '2025-12-31'],
                                'totalAssets' => ['raw' => 2000],
                            ]],
                        ],
                        'cashflowStatementHistory' => [
                            'cashflowStatements' => [[
                                'endDate' => ['fmt' => '2025-12-31'],
                                'totalCashFromOperatingActivities' => ['raw' => 300],
                            ]],
                        ],
                        'recommendationTrend' => [
                            'trend' => [['period' => '0m', 'buy' => 5]],
                        ],
                        'upgradeDowngradeHistory' => [
                            'history' => [['firm' => 'Broker A', 'toGrade' => 'Buy']],
                        ],
                    ]],
                ],
            ]),
            'https://yahoo.test/v1/finance/search*' => Http::response([
                'news' => [[
                    'title' => 'BBCA mencatat pertumbuhan laba',
                    'publisher' => 'Example News',
                    'link' => 'https://example.test/news',
                    'providerPublishTime' => 1767139200,
                ]],
            ]),
        ]);

        $summary = app(YahooStockDataService::class)->fetchSummary(new Stock(['kode' => 'BBCA']));

        $this->assertSame('Banks - Regional', $summary['profile']['industry']);
        $this->assertSame(21.5, $summary['stats']['trailingPE']);
        $this->assertSame(2000, $summary['financials'][0]['totalAssets']);
        $this->assertSame(300, $summary['financials'][0]['totalCashFromOperatingActivities']);
        $this->assertSame('buy', $summary['analysts']['recommendationKey']);
        $this->assertSame('Broker A', $summary['analysts']['upgradesDowngrades'][0]['firm']);
        $this->assertSame('BBCA mencatat pertumbuhan laba', $summary['news'][0]['title']);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/quoteSummary/BBCA.JK')
            && str_contains($request->url(), 'recommendationTrend'));
    }

    public function test_news_failure_does_not_hide_the_other_summary_data(): void
    {
        Http::fake([
            'https://yfapi.test/v11/finance/quoteSummary/*' => Http::response([
                'quoteSummary' => [
                    'result' => [[
                        'assetProfile' => ['industry' => 'Banks - Regional'],
                    ]],
                ],
            ]),
            'https://yahoo.test/v1/finance/search*' => Http::response([], 503),
        ]);

        $summary = app(YahooStockDataService::class)->fetchSummary(new Stock(['kode' => 'BBCA']));

        $this->assertSame('Banks - Regional', $summary['profile']['industry']);
        $this->assertSame([], $summary['news']);
    }
}
