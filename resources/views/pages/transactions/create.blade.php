@extends('layouts.app')

@section('title', 'Catat Transaksi')

@section('content')
    <div class="space-y-5">
        <div class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <x-icon name="plus" size="h-5 w-5" />
            </span>
            <div>
                <h1 class="text-lg font-semibold text-ink">Catat Transaksi</h1>
                <p class="text-xs text-subtle">Isi pemasukan atau pengeluaran baru.</p>
            </div>
        </div>

        <x-transaction-form
            :action="route('transactions.store')"
            method="POST"
            :wallets="$wallets"
            :category-options="$categoryOptions" />
    </div>
@endsection