<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh saldo dihitung dari tabel transactions (bukan kolom saldo statis).
 */
class BalanceCalculatorService
{
    /**
     * Total saldo semua dompet = total pemasukan - total pengeluaran.
     */
    public function totalBalance(): int
    {
        $row = DB::table('transactions')
            ->selectRaw($this->aggregateSelect())
            ->first();

        return (int) $row->income - (int) $row->expense;
    }

    /**
     * Saldo per dompet (dihitung dari transaksi dompet tersebut).
     *
     * @return Collection<int, array{wallet: Wallet, balance: int, transaction_count: int}>
     */
    public function walletBalances(): Collection
    {
        $aggregates = DB::table('transactions')
            ->selectRaw('wallet_id, COUNT(*) as transaction_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
            ->groupBy('wallet_id')
            ->get()
            ->keyBy('wallet_id');

        return Wallet::query()
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get()
            ->map(function (Wallet $wallet) use ($aggregates): array {
                $row = $aggregates->get($wallet->id);
                $income = (int) ($row->income ?? 0);
                $expense = (int) ($row->expense ?? 0);

                return [
                    'wallet' => $wallet,
                    'balance' => $income - $expense,
                    'transaction_count' => (int) ($row->transaction_count ?? 0),
                ];
            });
    }

    /**
     * Ringkasan bulan berjalan: pemasukan, pengeluaran, dan selisihnya.
     *
     * @return array{income: int, expense: int, net: int}
     */
    public function monthSummary(): array
    {
        $now = now();

        $row = DB::table('transactions')
            ->whereBetween('transaction_date', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ])
            ->selectRaw($this->aggregateSelect())
            ->first();

        $income = (int) $row->income;
        $expense = (int) $row->expense;

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
        ];
    }

    private function aggregateSelect(): string
    {
        return "COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income, "
            ."COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense";
    }
}