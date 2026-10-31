@props([
    'totalBalance' => 0,
    'currentMonthIncome' => 0,
    'currentMonthExpense' => 0,
    'periodLabel' => null,
])

@php
    $format = function (int|string $value): string {
        $value = (int) $value;

        return ($value < 0 ? '-Rp ' : 'Rp ').number_format(abs($value), 0, ',', '.');
    };
@endphp

<div class="rounded-2xl bg-gradient-to-br from-primary-light to-primary-dark p-6 text-white shadow-lg shadow-primary/20">
    <div class="flex items-start justify-between gap-3">
        <span class="text-sm text-white/80">Total Saldo Semua Dompet</span>
        @if ($periodLabel)
            <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium">{{ $periodLabel }}</span>
        @endif
    </div>

    <p class="mt-2 text-3xl font-bold tracking-tight">{{ $format($totalBalance) }}</p>

    <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-white/10 p-3">
            <div class="flex items-center gap-1.5 text-xs text-white/80">
                <x-icon name="arrow-up" size="h-3.5 w-3.5" class="text-income-soft" />
                Pemasukan Bulan Ini
            </div>
            <p class="mt-1 text-sm font-semibold">{{ $format($currentMonthIncome) }}</p>
        </div>
        <div class="rounded-xl bg-white/10 p-3">
            <div class="flex items-center gap-1.5 text-xs text-white/80">
                <x-icon name="arrow-down" size="h-3.5 w-3.5" class="text-expense-soft" />
                Pengeluaran Bulan Ini
            </div>
            <p class="mt-1 text-sm font-semibold">{{ $format($currentMonthExpense) }}</p>
        </div>
    </div>
</div>