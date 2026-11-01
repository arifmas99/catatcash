<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        return view('pages.transactions.index', [
            'transactions' => Transaction::query()
                ->with(['category', 'wallet'])
                ->orderByDesc('transaction_date')
                ->orderByDesc('transaction_time')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.transactions.create', $this->formData());
    }

    public function edit(Transaction $transaction): View
    {
        return view('pages.transactions.edit', $this->formData(['transaction' => $transaction]));
    }

    public function store(UpsertTransactionRequest $request): RedirectResponse
    {
        Transaction::create($this->payload($request));

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil dicatat.');
    }

    public function update(UpsertTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($this->payload($request));

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(UpsertTransactionRequest $request): array
    {
        $time = (string) $request->input('transaction_time');

        return [
            'wallet_id' => (int) $request->input('wallet_id'),
            'category_id' => (int) $request->input('category_id'),
            'type' => $request->input('type'),
            'amount' => (int) $request->input('amount'),
            'transaction_date' => $request->input('transaction_date'),
            'transaction_time' => strlen($time) === 5 ? $time.':00' : $time,
            'notes' => $request->input('notes') ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function formData(array $extra = []): array
    {
        return array_merge([
            'wallets' => Wallet::query()->orderByDesc('is_primary')->orderBy('name')->get(),
            'categoryOptions' => [
                'income' => Category::query()->where('type', 'income')->orderBy('id')->get(['id', 'name']),
                'expense' => Category::query()->where('type', 'expense')->orderBy('id')->get(['id', 'name']),
            ],
        ], $extra);
    }
}