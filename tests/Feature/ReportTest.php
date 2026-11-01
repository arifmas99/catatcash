<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePinIsUnlocked;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsurePinIsUnlocked::class);
    }

    private function tx(Wallet $wallet, Category $category, string $type, int $amount, string $date): void
    {
        Transaction::create([
            'wallet_id' => $wallet->id, 'category_id' => $category->id, 'type' => $type,
            'amount' => $amount, 'transaction_date' => $date,
            'transaction_time' => '10:00:00', 'notes' => null,
        ]);
    }

    /**
     * @return array{walletA: Wallet, walletB: Wallet}
     */
    private function setupData(): array
    {
        $walletA = Wallet::create(['name' => 'Dompet A', 'is_primary' => true]);
        $walletB = Wallet::create(['name' => 'Dompet B']);
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        $expense = Category::create(['name' => 'Makan & Minum', 'type' => 'expense']);

        $this->tx($walletA, $income, 'income', 100000, '2026-11-10');
        $this->tx($walletA, $expense, 'expense', 40000, '2026-11-11');
        $this->tx($walletB, $expense, 'expense', 25000, '2026-11-20');

        return ['walletA' => $walletA, 'walletB' => $walletB];
    }

    public function test_monthly_default_totals(): void
    {
        $this->setupData();

        $this->get('/reports?period=monthly&month=2026-11')
            ->assertOk()->assertSee('Rp 100.000')->assertSee('Rp 65.000')->assertSee('Rp 35.000');
    }

    public function test_daily_and_empty_state(): void
    {
        $this->setupData();

        $this->get('/reports?period=daily&date=2026-11-10')->assertOk()->assertSee('Rp 100.000');
        $this->get('/reports?period=daily&date=2026-11-15')
            ->assertOk()->assertSee('Belum ada data transaksi pada periode dan filter yang dipilih');
    }

    public function test_weekly_monday_to_sunday(): void
    {
        $this->setupData();

        $this->get('/reports?period=weekly&week_start=2026-11-09')
            ->assertOk()->assertSee('Rp 100.000')->assertSee('Rp 40.000');
    }

    public function test_custom_range_and_validation(): void
    {
        $this->setupData();

        $this->get('/reports?period=custom&start_date=2026-11-09&end_date=2026-11-12')
            ->assertOk()->assertSee('Rp 100.000')->assertSee('Rp 40.000');

        $this->get('/reports?period=custom&start_date=2026-11-20&end_date=2026-11-10')
            ->assertSessionHasErrors('end_date');
    }

    public function test_wallet_filter(): void
    {
        $data = $this->setupData();

        $this->get('/reports?period=monthly&month=2026-11&wallet_id='.$data['walletB']->id)
            ->assertOk()->assertSee('Rp 25.000')->assertDontSee('Rp 100.000');
    }

    public function test_category_group_filter(): void
    {
        $this->setupData();

        $this->get('/reports?period=monthly&month=2026-11&category_filter=type:income')
            ->assertOk()->assertSee('Rp 100.000')->assertDontSee('Rp 25.000');

        $this->get('/reports?period=monthly&month=2026-11&category_filter=type:expense')
            ->assertOk()->assertSee('Rp 65.000');
    }

    public function test_report_page_renders(): void
    {
        $this->setupData();

        $this->get('/reports')
            ->assertOk()->assertSee('Total Pemasukan')->assertSee('Total Pengeluaran')->assertSee('Laba Bersih');
    }
}