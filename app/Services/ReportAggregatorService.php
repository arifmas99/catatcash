<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Perhitungan ringkasan laporan (total pemasukan, pengeluaran, laba bersih)
 * beserta penyaringan transaksi sesuai filter periode, dompet, dan kategori.
 */
class ReportAggregatorService
{
    /**
     * Rentang tanggal inklusif sesuai filter periode.
     *
     * @param  array<string, mixed>  $filters
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function resolveRange(array $filters): array
    {
        return match ($filters['period'] ?? 'monthly') {
            'daily' => $this->dayRange($filters['date'] ?? null),
            'weekly' => $this->weekRange($filters['week_start'] ?? null),
            'custom' => $this->customRange($filters['start_date'] ?? null, $filters['end_date'] ?? null),
            default => $this->monthRange($filters['month'] ?? null),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function baseQuery(array $filters): Builder
    {
        $range = $this->resolveRange($filters);

        $query = Transaction::query()
            ->whereBetween('transaction_date', [$range['start'], $range['end']]);

        $wallet = $filters['wallet_id'] ?? 'all';
        if ($wallet !== null && $wallet !== '' && $wallet !== 'all') {
            $query->where('wallet_id', (int) $wallet);
        }

        $category = $filters['category_filter'] ?? 'all';
        if ($category === 'type:income') {
            $query->where('type', 'income');
        } elseif ($category === 'type:expense') {
            $query->where('type', 'expense');
        } elseif ($category !== null && $category !== '' && $category !== 'all') {
            $query->where('category_id', (int) $category);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{income: int, expense: int, net: int}
     */
    public function summary(array $filters): array
    {
        $query = $this->baseQuery($filters);
        $income = (int) (clone $query)->where('type', 'income')->sum('amount');
        $expense = (int) (clone $query)->where('type', 'expense')->sum('amount');

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Transaction>
     */
    public function transactions(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->with(['category', 'wallet'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    private function dayRange(?string $date): array
    {
        $day = $date ? CarbonImmutable::parse($date) : CarbonImmutable::today();

        return ['start' => $day->startOfDay(), 'end' => $day->endOfDay()];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    private function weekRange(?string $weekStart): array
    {
        $start = ($weekStart ? CarbonImmutable::parse($weekStart) : CarbonImmutable::today())
            ->startOfWeek(CarbonInterface::MONDAY)
            ->startOfDay();

        return ['start' => $start, 'end' => $start->addDays(6)->endOfDay()];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    private function monthRange(?string $month): array
    {
        $base = $month ? CarbonImmutable::createFromFormat('!Y-m', $month) : CarbonImmutable::now();

        return ['start' => $base->startOfMonth()->startOfDay(), 'end' => $base->endOfMonth()->endOfDay()];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    private function customRange(?string $start, ?string $end): array
    {
        return [
            'start' => CarbonImmutable::parse($start)->startOfDay(),
            'end' => CarbonImmutable::parse($end)->endOfDay(),
        ];
    }
}