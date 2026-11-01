@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                    <x-icon name="list" size="h-5 w-5" />
                </span>
                <div>
                    <h1 class="text-lg font-semibold text-ink">Transaksi</h1>
                    <p class="text-xs text-subtle">Semua catatan pemasukan dan pengeluaran.</p>
                </div>
            </div>
            <a href="{{ route('transactions.create') }}"
               class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110 active:scale-95">
                <x-icon name="plus" size="h-4 w-4" />
                Catat
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-income-soft px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if ($transactions->isEmpty())
            <x-empty-state
                title="Belum Ada Transaksi"
                message="Mulai catat pemasukan atau pengeluaran pertama Anda."
                :action-url="route('transactions.create')"
                action-label="Catat Transaksi" />
        @else
            <div class="space-y-2.5">
                @foreach ($transactions as $transaction)
                    <x-transaction-item :transaction="$transaction" />
                @endforeach
            </div>
        @endif
    </div>
@endsection