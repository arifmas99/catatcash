<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\BalanceCalculatorService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BalanceCalculatorService $balance)
    {
    }

    public function index(): View
    {
        $summary = $this->balance->monthSummary();

        $recentTransactions = Transaction::query()
            ->with(['category', 'wallet'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('pages.dashboard.index', [
            'totalBalance' => $this->balance->totalBalance(),
            'walletBalances' => $this->balance->walletBalances(),
            'monthIncome' => $summary['income'],
            'monthExpense' => $summary['expense'],
            'monthNet' => $summary['net'],
            'periodLabel' => $this->periodLabel(),
            'recentTransactions' => $recentTransactions,
        ]);
    }

    private function periodLabel(): string
    {
        $months = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $now = now();

        return $months[(int) $now->month].' '.$now->year;
    }
}