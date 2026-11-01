<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type') === 'expense' ? 'expense' : 'income';

        $categories = Category::query()
            ->where('type', $type)
            ->withCount('transactions')
            ->orderBy('id')
            ->get()
            ->map(function (Category $category): array {
                $count = (int) $category->transactions_count;

                return [
                    'category' => $category,
                    'transaction_count' => $count,
                    'can_delete' => $count === 0,
                    'delete_reason' => $count > 0
                        ? "Kategori '{$category->name}' tidak dapat dihapus karena sedang digunakan oleh {$count} transaksi. Silakan ubah kategori pada transaksi terkait terlebih dahulu."
                        : null,
                ];
            });

        return view('pages.categories.index', [
            'activeType' => $type,
            'categories' => $categories,
            'counts' => [
                'income' => Category::query()->where('type', 'income')->count(),
                'expense' => Category::query()->where('type', 'expense')->count(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $type = $request->input('type');

        Category::create([
            'name' => $request->input('name'),
            'type' => $type,
        ]);

        return redirect()->route('categories.index', ['type' => $type])
            ->with('status', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update(['name' => $request->input('name')]);

        return redirect()->route('categories.index', ['type' => $category->type->value])
            ->with('status', "Nama kategori berhasil diubah menjadi '{$category->name}'.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $transactionCount = $category->transactions()->count();

        if ($transactionCount > 0) {
            return redirect()->route('categories.index', ['type' => $category->type->value])
                ->withErrors([
                    'delete' => "Kategori '{$category->name}' tidak dapat dihapus karena sedang digunakan oleh {$transactionCount} transaksi. Silakan ubah kategori pada transaksi terkait terlebih dahulu.",
                ]);
        }

        $name = $category->name;
        $type = $category->type->value;
        $category->delete();

        return redirect()->route('categories.index', ['type' => $type])
            ->with('status', "Kategori '{$name}' berhasil dihapus.");
    }
}