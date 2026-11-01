<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePinIsUnlocked;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ReportAggregatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsurePinIsUnlocked::class);
    }

    private function seedData(): void
    {
        $wallet = Wallet::create(['name' => 'Dompet Utama', 'is_primary' => true]);
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        $expense = Category::create(['name' => 'Makan & Minum', 'type' => 'expense']);

        $this->tx($wallet, $income, 'income', 100000, '2026-11-01');
        $this->tx($wallet, $expense, 'expense', 40000, '2026-11-02');
        $this->tx($wallet, $income, 'income', 50000, '2026-11-05');
    }

    private function tx(Wallet $wallet, Category $category, string $type, int $amount, string $date): void
    {
        Transaction::create([
            'wallet_id' => $wallet->id, 'category_id' => $category->id, 'type' => $type,
            'amount' => $amount, 'transaction_date' => $date,
            'transaction_time' => '10:00:00', 'notes' => null,
        ]);
    }

    private function series(array $filters): array
    {
        return app(ReportAggregatorService::class)->chartSeries($filters + ['wallet_id' => 'all', 'category_filter' => 'all']);
    }

    public function test_monthly_series_one_point_per_day(): void
    {
        $this->seedData();
        $chart = $this->series(['period' => 'monthly', 'month' => '2026-11']);

        $this->assertCount(30, $chart['labels']);
        $this->assertSame('daily', $chart['grouping']);
        $this->assertSame('line', $chart['auto_type']);
        $this->assertSame(100000, $chart['income'][0]);
        $this->assertSame(40000, $chart['expense'][1]);
        $this->assertSame(50000, $chart['income'][4]);
    }

    public function test_daily_series_single_point(): void
    {
        $this->seedData();
        $chart = $this->series(['period' => 'daily', 'date' => '2026-11-01']);

        $this->assertSame(['01/11/2026'], $chart['labels']);
        $this->assertSame(100000, $chart['income'][0]);
        $this->assertSame('bar', $chart['auto_type']);
    }

    public function test_weekly_series_seven_days(): void
    {
        $this->seedData();
        $chart = $this->series(['period' => 'weekly', 'week_start' => '2026-10-26']);

        $this->assertCount(7, $chart['labels']);
        $this->assertSame(100000, $chart['income'][6]);
        $this->assertSame('bar', $chart['auto_type']);
    }

    public function test_custom_grouping_rules(): void
    {
        $this->seedData();

        $short = $this->series(['period' => 'custom', 'start_date' => '2026-11-01', 'end_date' => '2026-11-10']);
        $this->assertCount(10, $short['labels']);
        $this->assertSame('daily', $short['grouping']);
        $this->assertSame('bar', $short['auto_type']);

        $medium = $this->series(['period' => 'custom', 'start_date' => '2026-01-01', 'end_date' => '2026-05-31']);
        $this->assertSame('weekly', $medium['grouping']);
        $this->assertSame('bar', $medium['auto_type']);

        $long = $this->series(['period' => 'custom', 'start_date' => '2024-01-01', 'end_date' => '2026-06-30']);
        $this->assertSame('monthly', $long['grouping']);
        $this->assertSame('line', $long['auto_type']);
        $this->assertCount(30, $long['labels']);
    }

    public function test_page_shows_chart_or_empty_message(): void
    {
        $this->seedData();

        $this->get('/reports?period=monthly&month=2026-11')
            ->assertOk()
            ->assertSee('Grafik Tren Alur Kas')
            ->assertSee('cashflow-chart', false)
            ->assertSee('data-chart-type="auto"', false)
            ->assertSee('data-chart-type="line"', false)
            ->assertSee('data-chart-type="bar"', false);

        $this->get('/reports?period=daily&date=2026-12-31')
            ->assertOk()
            ->assertSee('Belum ada data transaksi pada periode dan filter yang dipilih')
            ->assertDontSee('cashflow');
    }
}