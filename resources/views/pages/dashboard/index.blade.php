@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        $format = function (int $value): string {
            return ($value < 0 ? '-Rp ' : 'Rp ').number_format(abs($value), 0, ',', '.');
        };

        $allTransactionsUrl = \Illuminate\Support\Facades\Route::has('transactions.index')
            ? route('transactions.index')
            : '#';
    @endphp

    <div class="space-y-5">
        {{-- Kartu Total Saldo (membentang penuh) --}}
        <x-summary-balance-card
            :total-balance="$totalBalance"
            :current-month-income="$monthIncome"
            :current-month-expense="$monthExpense"
            :period-label="$periodLabel" />

        <p class="px-1 text-sm text-subtle">
            Selisih bulan ini: <span class="font-semibold {{ $monthNet < 0 ? 'text-expense' : 'text-income' }}">{{ $format($monthNet) }}</span>
        </p>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Kolom kiri --}}
            <div class="space-y-5">
                {{-- Dompet Saya --}}
                <section class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-ink">
                            <x-icon name="wallet" size="h-4 w-4" class="text-primary" />
                            Dompet Saya
                        </h2>
                        <span class="text-xs text-subtle">{{ $walletBalances->count() }} dompet</span>
                    </div>

                    @if ($walletBalances->isEmpty())
                        <x-empty-state title="Belum Ada Dompet" message="Tambahkan dompet terlebih dahulu." />
                    @else
                        <div class="space-y-2.5">
                            @foreach ($walletBalances as $item)
                                <x-wallet-card
                                    :wallet-name="$item['wallet']->name"
                                    :calculated-balance="$item['balance']"
                                    :is-primary="$item['wallet']->is_primary"
                                    :transaction-count="$item['transaction_count']" />
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Segera Hadir (tidak bisa diklik) --}}
                <section class="space-y-3">
                    <x-coming-soon-card
                        title="Pantauan Anggaran"
                        description="Atur anggaran per kategori. Segera hadir di versi berikutnya." />
                    <x-coming-soon-card
                        title="Target Tabungan"
                        description="Tentukan target dan pantau progres tabungan. Segera hadir." />
                </section>
            </div>

            {{-- Kolom kanan --}}
            <div class="space-y-5">
                {{-- Transaksi Terbaru (5 baris) --}}
                <section class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-ink">
                            <x-icon name="list" size="h-4 w-4" class="text-primary" />
                            Transaksi Terbaru
                        </h2>
                        <a href="{{ $allTransactionsUrl }}"
                           class="inline-flex items-center gap-0.5 text-xs font-medium text-primary transition hover:text-primary-dark active:scale-95">
                            Lihat Semua
                            <x-icon name="chevron-right" size="h-3.5 w-3.5" />
                        </a>
                    </div>

                    @if ($recentTransactions->isEmpty())
                        <x-empty-state
                            title="Belum Ada Transaksi"
                            message="Mulai catat pemasukan atau pengeluaran pertama Anda." />
                    @else
                        <div class="space-y-2.5">
                            @foreach ($recentTransactions as $transaction)
                                <x-transaction-item :transaction="$transaction" />
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection