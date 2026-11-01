@extends('layouts.app')

@section('title', 'Ubah Transaksi')

@section('content')
    <div class="space-y-5">
        <div class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <x-icon name="edit" size="h-5 w-5" />
            </span>
            <div>
                <h1 class="text-lg font-semibold text-ink">Ubah Transaksi</h1>
                <p class="text-xs text-subtle">Perbarui data transaksi ini.</p>
            </div>
        </div>

        <x-transaction-form
            :action="route('transactions.update', $transaction)"
            method="PUT"
            :transaction="$transaction"
            :wallets="$wallets"
            :category-options="$categoryOptions" />
    </div>
@endsection