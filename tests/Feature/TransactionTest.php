<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePinIsUnlocked;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\BalanceCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsurePinIsUnlocked::class);
    }

    private function wallet(): Wallet
    {
        return Wallet::create(['name' => 'Dompet Utama', 'is_primary' => true]);
    }

    private function tx(Wallet $wallet, Category $category, string $type, int $amount): Transaction
    {
        return Transaction::create([
            'wallet_id' => $wallet->id, 'category_id' => $category->id, 'type' => $type,
            'amount' => $amount, 'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00', 'notes' => null,
        ]);
    }

    public function test_create_income_and_expense_updates_balance(): void
    {
        $wallet = $this->wallet();
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        $expense = Category::create(['name' => 'Makan & Minum', 'type' => 'expense']);
        $svc = app(BalanceCalculatorService::class);

        $this->post('/transactions', [
            'type' => 'income', 'amount' => 100000, 'wallet_id' => $wallet->id, 'category_id' => $income->id,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '10:00', 'notes' => 'gaji',
        ])->assertRedirect(route('transactions.index'));

        $this->post('/transactions', [
            'type' => 'expense', 'amount' => 40000, 'wallet_id' => $wallet->id, 'category_id' => $expense->id,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '11:30', 'notes' => null,
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(2, Transaction::count());
        $this->assertSame(60000, $svc->totalBalance());
        $this->assertSame(60000, $svc->walletBalances()->first()['balance']);
    }

    public function test_update_transaction_changes_balance(): void
    {
        $wallet = $this->wallet();
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        $transaction = $this->tx($wallet, $income, 'income', 100000);
        $svc = app(BalanceCalculatorService::class);
        $this->assertSame(100000, $svc->totalBalance());

        $this->put("/transactions/{$transaction->id}", [
            'type' => 'income', 'amount' => 250000, 'wallet_id' => $wallet->id, 'category_id' => $income->id,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '09:15', 'notes' => 'naik',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(250000, $svc->totalBalance());
    }

    public function test_delete_transaction_changes_balance(): void
    {
        $wallet = $this->wallet();
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        $transaction = $this->tx($wallet, $income, 'income', 70000);
        $svc = app(BalanceCalculatorService::class);

        $this->delete("/transactions/{$transaction->id}")->assertRedirect(route('transactions.index'));

        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, $svc->totalBalance());
    }

    public function test_amount_zero_and_negative_rejected(): void
    {
        $wallet = $this->wallet();
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);

        foreach ([0, -5000] as $amount) {
            $this->from('/transactions/create')->post('/transactions', [
                'type' => 'income', 'amount' => $amount, 'wallet_id' => $wallet->id, 'category_id' => $income->id,
                'transaction_date' => now()->toDateString(), 'transaction_time' => '10:00', 'notes' => null,
            ])->assertSessionHasErrors('amount');
        }

        $this->assertSame(0, Transaction::count());
    }

    public function test_category_type_mismatch_rejected(): void
    {
        $wallet = $this->wallet();
        $expense = Category::create(['name' => 'Makan & Minum', 'type' => 'expense']);

        $this->from('/transactions/create')->post('/transactions', [
            'type' => 'income', 'amount' => 5000, 'wallet_id' => $wallet->id, 'category_id' => $expense->id,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '10:00', 'notes' => null,
        ])->assertSessionHasErrors('category_id');

        $this->assertSame(0, Transaction::count());
    }

    public function test_invalid_wallet_or_category_rejected(): void
    {
        $wallet = $this->wallet();
        $income = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);

        $this->from('/transactions/create')->post('/transactions', [
            'type' => 'income', 'amount' => 5000, 'wallet_id' => 999999, 'category_id' => $income->id,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '10:00', 'notes' => null,
        ])->assertSessionHasErrors('wallet_id');

        $this->from('/transactions/create')->post('/transactions', [
            'type' => 'income', 'amount' => 5000, 'wallet_id' => $wallet->id, 'category_id' => 999999,
            'transaction_date' => now()->toDateString(), 'transaction_time' => '10:00', 'notes' => null,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_pages_render(): void
    {
        $this->wallet();
        Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);
        Category::create(['name' => 'Makan & Minum', 'type' => 'expense']);

        $this->get('/transactions')->assertOk()->assertSee('Transaksi');
        $this->get('/transactions/create')
            ->assertOk()->assertSee('Catat Transaksi')->assertSee('Pemasukan')->assertSee('Pengeluaran');
    }
}