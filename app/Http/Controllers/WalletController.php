<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Models\Wallet;
use App\Services\BalanceCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private readonly BalanceCalculatorService $balance)
    {
    }

    public function index(): View
    {
        $walletBalances = $this->balance->walletBalances();
        $walletCount = $walletBalances->count();

        $walletBalances = $walletBalances->map(function (array $item) use ($walletCount): array {
            $transactions = $item['transaction_count'];
            $canDelete = $walletCount > 1 && $transactions === 0;

            $reason = null;
            if (! $canDelete) {
                $reason = $transactions > 0
                    ? "Dompet '{$item['wallet']->name}' masih memiliki {$transactions} transaksi."
                    : 'Minimal harus ada satu dompet aktif di dalam aplikasi.';
            }

            return $item + ['can_delete' => $canDelete, 'delete_reason' => $reason];
        });

        return view('pages.wallets.index', [
            'walletBalances' => $walletBalances,
            'walletCount' => $walletCount,
        ]);
    }

    public function store(StoreWalletRequest $request): RedirectResponse
    {
        Wallet::create([
            'name' => $request->input('name'),
            'is_primary' => false,
        ]);

        return redirect()->route('wallets.index')->with('status', 'Dompet baru berhasil ditambahkan.');
    }

    public function update(StoreWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $wallet->update(['name' => $request->input('name')]);

        return redirect()->route('wallets.index')
            ->with('status', "Nama dompet berhasil diubah menjadi '{$wallet->name}'.");
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $transactionCount = $wallet->transactions()->count();

        if ($transactionCount > 0) {
            return redirect()->route('wallets.index')->withErrors([
                'delete' => "Dompet '{$wallet->name}' tidak dapat dihapus karena masih memiliki {$transactionCount} transaksi.",
            ]);
        }

        if (Wallet::count() <= 1) {
            return redirect()->route('wallets.index')->withErrors([
                'delete' => 'Minimal harus ada satu dompet aktif di dalam aplikasi.',
            ]);
        }

        $wasPrimary = $wallet->is_primary;
        $name = $wallet->name;
        $wallet->delete();

        if ($wasPrimary) {
            $replacement = Wallet::query()->orderBy('id')->first();

            if ($replacement !== null) {
                $replacement->update(['is_primary' => true]);
            }
        }

        return redirect()->route('wallets.index')->with('status', "Dompet '{$name}' berhasil dihapus.");
    }
}