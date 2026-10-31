@props([
    'walletName',
    'calculatedBalance' => 0,
    'isPrimary' => false,
    'transactionCount' => 0,
])

@php
    $balance = (int) $calculatedBalance;
    $format = ($balance < 0 ? '-Rp ' : 'Rp ').number_format(abs($balance), 0, ',', '.');
@endphp

<div class="flex items-center gap-3 rounded-2xl border border-line bg-surface p-4 shadow-sm transition hover:shadow-md">
    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
        <x-icon name="wallet" size="h-5 w-5" />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="truncate text-sm font-semibold text-ink">{{ $walletName }}</p>
            @if ($isPrimary)
                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary">Utama</span>
            @endif
        </div>
        <p class="text-xs text-subtle">{{ $transactionCount }} transaksi</p>
    </div>

    <p class="text-sm font-semibold {{ $balance < 0 ? 'text-expense' : 'text-ink' }}">{{ $format }}</p>
</div>