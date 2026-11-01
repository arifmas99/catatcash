@props(['transaction'])

@php
    $category = $transaction->category;
    $isIncome = $transaction->type === \App\Enums\TransactionType::Income;
    $amount = (int) $transaction->amount;
    $date = $transaction->transaction_date;
    $time = $transaction->transaction_time;
    $icon = $category
        ? \App\Support\CategoryIconResolver::resolve($category->name, $category->type)
        : ($isIncome ? 'arrow-up' : 'arrow-down');
    $editUrl = \Illuminate\Support\Facades\Route::has('transactions.edit')
        ? route('transactions.edit', $transaction)
        : '#';
@endphp

<div class="flex items-start gap-3 rounded-2xl border border-line bg-surface p-3 shadow-sm">
    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $isIncome ? 'bg-income-soft text-income' : 'bg-expense-soft text-expense' }}">
        <x-icon :name="$icon" size="h-5 w-5" />
    </span>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-ink">{{ $transaction->category?->name }}</p>
        <p class="text-xs text-subtle">
            {{ $transaction->wallet?->name }}
            @if ($date) · {{ $date->format('d/m/Y') }} @endif
            @if ($time) {{ substr((string) $time, 0, 5) }} @endif
        </p>
        @if ($transaction->notes)
            <p class="truncate text-xs text-subtle">{{ $transaction->notes }}</p>
        @endif
    </div>

    <div class="flex flex-col items-end gap-1.5">
        <p class="whitespace-nowrap text-sm font-semibold {{ $isIncome ? 'text-income' : 'text-expense' }}">
            {{ $isIncome ? '+' : '-' }} Rp {{ number_format($amount, 0, ',', '.') }}
        </p>
        <div class="flex items-center gap-1">
            <a href="{{ $editUrl }}"
               aria-label="Ubah transaksi"
               class="grid h-7 w-7 place-items-center rounded-lg text-subtle transition hover:bg-muted hover:text-primary active:scale-90">
                <x-icon name="edit" size="h-4 w-4" />
            </a>
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                  onsubmit="return confirm('Hapus transaksi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        aria-label="Hapus transaksi"
                        class="grid h-7 w-7 place-items-center rounded-lg text-subtle transition hover:bg-expense-soft hover:text-expense active:scale-90">
                    <x-icon name="trash" size="h-4 w-4" />
                </button>
            </form>
        </div>
    </div>
</div>