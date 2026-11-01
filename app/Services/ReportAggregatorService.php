<?php

namespace App\Services;

use App\Enums\TransactionType;
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

    /**
     * Deret grafik Tren Alur Kas memakai filter yang sama dengan ringkasan.
     *
     * @param  array<string, mixed>  $filters
     * @return array{labels: array<int, string>, income: array<int, int>, expense: array<int, int>, count: int, grouping: string, auto_type: string}
     */
    public function chartSeries(array $filters): array
    {
        $period = $filters['period'] ?? 'monthly';
        [$grouping, $buckets] = $this->bucketPlan($period, $this->resolveRange($filters));

        $series = [
            'income' => array_fill(0, count($buckets), 0),
            'expense' => array_fill(0, count($buckets), 0),
        ];

        $rows = $this->baseQuery($filters)->get(['transaction_date', 'type', 'amount']);

        foreach ($rows as $row) {
            $index = $this->bucketIndex($buckets, CarbonImmutable::parse($row->transaction_date)->startOfDay());

            if ($index === null) {
                continue;
            }

            $series[$row->type === TransactionType::Income ? 'income' : 'expense'][$index] += (int) $row->amount;
        }

        return [
            'labels' => array_map(fn (array $bucket): string => $bucket['label'], $buckets),
            'income' => $series['income'],
            'expense' => $series['expense'],
            'count' => $rows->count(),
            'grouping' => $grouping,
            'auto_type' => $this->autoChartType($period, $grouping),
        ];
    }

    /**
     * Rencana ember sumbu X: [granularitas, daftar ember].
     *
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return array{0: string, 1: array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>}
     */
    private function bucketPlan(string $period, array $range): array
    {
        return match ($period) {
            'daily' => ['daily', $this->dayBuckets($range['start'], $range['end'], 'd/m/Y')],
            'weekly' => ['daily', $this->dayBuckets($range['start'], $range['end'], 'd/m')],
            'monthly' => ['daily', $this->dayBuckets($range['start'], $range['end'], 'j')],
            default => $this->customPlan($range['start'], $range['end']),
        };
    }

    /**
     * Aturan pengelompokan Custom: <=31 hari per hari, 32-180 hari per minggu, >180 hari per bulan.
     *
     * @return array{0: string, 1: array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>}
     */
    private function customPlan(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $days = (int) $start->diffInDays($end) + 1;

        if ($days <= 31) {
            return ['daily', $this->dayBuckets($start, $end, 'd/m')];
        }

        if ($days <= 180) {
            return ['weekly', $this->weekBuckets($start, $end)];
        }

        return ['monthly', $this->monthBuckets($start, $end)];
    }

    /**
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>
     */
    private function dayBuckets(CarbonImmutable $start, CarbonImmutable $end, string $format): array
    {
        $buckets = [];
        $cursor = $start->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets[] = ['start' => $cursor->startOfDay(), 'end' => $cursor->endOfDay(), 'label' => $cursor->format($format)];
            $cursor = $cursor->addDay();
        }

        return $buckets;
    }

    /**
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>
     */
    private function weekBuckets(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $buckets = [];
        $cursor = $start->startOfWeek(CarbonInterface::MONDAY);

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets[] = ['start' => $cursor->startOfDay(), 'end' => $cursor->addDays(6)->endOfDay(), 'label' => $cursor->format('d/m')];
            $cursor = $cursor->addWeek();
        }

        return $buckets;
    }

    /**
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>
     */
    private function monthBuckets(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $names = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $buckets = [];
        $cursor = $start->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets[] = ['start' => $cursor->startOfDay(), 'end' => $cursor->endOfMonth()->endOfDay(), 'label' => $names[(int) $cursor->month].' '.$cursor->year];
            $cursor = $cursor->addMonth();
        }

        return $buckets;
    }

    /**
     * @param  array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>  $buckets
     */
    private function bucketIndex(array $buckets, CarbonImmutable $date): ?int
    {
        foreach ($buckets as $index => $bucket) {
            if ($date->greaterThanOrEqualTo($bucket['start']) && $date->lessThanOrEqualTo($bucket['end'])) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Jenis grafik "Otomatis": Batang untuk Harian/Mingguan, Garis untuk Bulanan,
     * dan untuk Custom mengikuti pengelompokannya.
     */
    private function autoChartType(string $period, string $grouping): string
    {
        return match ($period) {
            'daily', 'weekly' => 'bar',
            'monthly' => 'line',
            default => in_array($grouping, ['daily', 'weekly'], true) ? 'bar' : 'line',
        };
    }
}