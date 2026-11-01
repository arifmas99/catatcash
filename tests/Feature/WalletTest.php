<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePinIsUnlocked;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsurePinIsUnlocked::class);
    }

    private function wallet(string $name, bool $primary = false): Wallet
    {
        return Wallet::create(['name' => $name, 'is_primary' => $primary]);
    }

    public function test_wallets_page_shows(): void
    {
        $this->wallet('Dompet Utama', true);

        $this->get('/wallets')->assertOk()->assertSee('Kelola Dompet')->assertSee('Dompet Utama');
    }

    public function test_add_wallet_and_duplicate_rejected(): void
    {
        $this->wallet('Dompet Utama', true);

        $this->post('/wallets', ['name' => 'Dompet Baru'])->assertRedirect(route('wallets.index'));
        $this->assertDatabaseHas('wallets', ['name' => 'Dompet Baru']);

        $this->from('/wallets')->post('/wallets', ['name' => 'Dompet Baru'])->assertSessionHasErrors('name');
        $this->assertSame(1, Wallet::where('name', 'Dompet Baru')->count());
    }

    public function test_edit_wallet_name(): void
    {
        $wallet = $this->wallet('Dompet Utama', true);

        $this->put("/wallets/{$wallet->id}", ['name' => 'Dompet Rumah'])
            ->assertRedirect(route('wallets.index'));

        $this->assertSame('Dompet Rumah', $wallet->fresh()->name);
    }

    public function test_delete_empty_wallet_reassigns_primary(): void
    {
        $primary = $this->wallet('Dompet Utama', true);
        $second = $this->wallet('Dompet Baru');

        $this->delete("/wallets/{$primary->id}")->assertRedirect(route('wallets.index'));

        $this->assertDatabaseMissing('wallets', ['id' => $primary->id]);
        $this->assertTrue($second->fresh()->is_primary);
    }

    public function test_cannot_delete_last_wallet(): void
    {
        $only = $this->wallet('Dompet Utama', true);

        $this->from('/wallets')->delete("/wallets/{$only->id}")->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('wallets', ['id' => $only->id]);
    }

    public function test_cannot_delete_wallet_with_transactions(): void
    {
        $wallet = $this->wallet('Dompet Utama', true);
        $this->wallet('Dompet Lain');
        $category = Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);

        Transaction::create([
            'wallet_id' => $wallet->id, 'category_id' => $category->id, 'type' => 'income',
            'amount' => 1000, 'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00', 'notes' => null,
        ]);

        $this->from('/wallets')->delete("/wallets/{$wallet->id}")->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('wallets', ['id' => $wallet->id]);
        $this->assertDatabaseHas('transactions', ['wallet_id' => $wallet->id]);
    }
}