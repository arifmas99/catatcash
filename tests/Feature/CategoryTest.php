<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Http\Middleware\EnsurePinIsUnlocked;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Support\CategoryIconResolver;
use Database\Seeders\InitialCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsurePinIsUnlocked::class);
    }

    public function test_seeder_has_eleven_categories(): void
    {
        $this->seed(InitialCategorySeeder::class);

        $this->assertSame(11, Category::count());
        $this->assertSame(5, Category::where('type', 'income')->count());
        $this->assertSame(6, Category::where('type', 'expense')->count());
    }

    public function test_icon_resolver_keys_by_type_and_name(): void
    {
        $this->assertSame('banknotes', CategoryIconResolver::resolve('Gaji & Bonus', TransactionType::Income));
        $this->assertSame('mug', CategoryIconResolver::resolve('Makan & Minum', TransactionType::Expense));
        $this->assertSame('ellipsis', CategoryIconResolver::resolve('Lainnya', TransactionType::Income));
        $this->assertSame('ellipsis', CategoryIconResolver::resolve('Lainnya', TransactionType::Expense));

        // Nama bawaan di jenis berbeda -> ikon jenis (bukan ikon bawaan).
        $this->assertSame('arrow-down', CategoryIconResolver::resolve('Gaji & Bonus', TransactionType::Expense));
        $this->assertSame('arrow-up', CategoryIconResolver::resolve('Makan & Minum', TransactionType::Income));
        $this->assertSame('arrow-up', CategoryIconResolver::resolve('Kategori Baru', TransactionType::Income));
        $this->assertSame('arrow-down', CategoryIconResolver::resolve('Kategori Baru', TransactionType::Expense));
    }

    public function test_add_category(): void
    {
        Category::create(['name' => 'Gaji & Bonus', 'type' => 'income']);

        $this->post('/categories', ['name' => 'Bonus Tahunan', 'type' => 'income'])
            ->assertRedirect(route('categories.index', ['type' => 'income']));

        $this->assertDatabaseHas('categories', ['name' => 'Bonus Tahunan', 'type' => 'income']);
    }

    public function test_duplicate_in_same_type_rejected(): void
    {
        Category::create(['name' => 'Bonus', 'type' => 'income']);

        $this->from('/categories?type=income')
            ->post('/categories', ['name' => 'Bonus', 'type' => 'income'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Category::where('name', 'Bonus')->where('type', 'income')->count());
    }

    public function test_same_name_in_different_type_allowed(): void
    {
        Category::create(['name' => 'Bonus', 'type' => 'income']);

        $this->post('/categories', ['name' => 'Bonus', 'type' => 'expense'])
            ->assertRedirect(route('categories.index', ['type' => 'expense']));

        $this->assertSame(1, Category::where('name', 'Bonus')->where('type', 'expense')->count());
    }

    public function test_edit_category_name(): void
    {
        $category = Category::create(['name' => 'Bonus', 'type' => 'income']);

        $this->put("/categories/{$category->id}", ['name' => 'Bonus Tahunan'])
            ->assertRedirect(route('categories.index', ['type' => 'income']));

        $this->assertSame('Bonus Tahunan', $category->fresh()->name);
    }

    public function test_delete_empty_category(): void
    {
        $category = Category::create(['name' => 'Bonus', 'type' => 'income']);

        $this->delete("/categories/{$category->id}")
            ->assertRedirect(route('categories.index', ['type' => 'income']));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_transactions(): void
    {
        $wallet = Wallet::create(['name' => 'Dompet Utama', 'is_primary' => true]);
        $category = Category::create(['name' => 'Bonus', 'type' => 'income']);

        Transaction::create([
            'wallet_id' => $wallet->id, 'category_id' => $category->id, 'type' => 'income',
            'amount' => 1000, 'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00', 'notes' => null,
        ]);

        $this->from('/categories?type=income')
            ->delete("/categories/{$category->id}")
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_index_lists_categories_per_type(): void
    {
        $this->seed(InitialCategorySeeder::class);

        $this->get('/categories?type=income')
            ->assertOk()->assertSee('Kelola Kategori')->assertSee('Pemasukan (5)')->assertSee('Gaji & Bonus')
            ->assertDontSee('Makan & Minum');

        $this->get('/categories?type=expense')
            ->assertOk()->assertSee('Makan & Minum')->assertDontSee('Gaji & Bonus');
    }
}