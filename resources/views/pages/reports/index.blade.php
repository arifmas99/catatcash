@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    @php
        $format = fn (int $value): string => ($value < 0 ? '-Rp ' : 'Rp ').number_format(abs($value), 0, ',', '.');
        $inputClass = 'w-full rounded-xl border border-line px-3 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-primary/40';
    @endphp

    <div class="space-y-5">
        <div class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <x-icon name="chart" size="h-5 w-5" />
            </span>
            <div>
                <h1 class="text-lg font-semibold text-ink">Laporan</h1>
                <p class="text-xs text-subtle">Filter periode, dompet, dan kategori.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <form method="GET" action="{{ route('reports.index') }}" x-data="{ period: '{{ $filters['period'] }}' }"
              class="space-y-4 rounded-2xl border border-line bg-surface p-4 shadow-sm sm:p-6">
            <div>
                <span class="mb-2 block text-sm font-semibold text-ink">Periode</span>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'custom' => 'Custom'] as $value => $label)
                        <button type="button" @click="period = '{{ $value }}'"
                                :class="period === '{{ $value }}' ? 'bg-primary text-white shadow-sm' : 'border border-line text-subtle hover:bg-muted'"
                                class="rounded-xl px-3 py-2 text-sm font-semibold transition active:scale-95">{{ $label }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="period" :value="period">
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div x-show="period === 'daily'">
                    <label for="date" class="mb-1.5 block text-sm font-semibold text-ink">Tanggal</label>
                    <input id="date" name="date" type="date" value="{{ $filters['date'] }}" :disabled="period !== 'daily'" class="{{ $inputClass }}">
                </div>
                <div x-show="period === 'weekly'">
                    <label for="week_start" class="mb-1.5 block text-sm font-semibold text-ink">Tanggal di minggu (Senin–Minggu)</label>
                    <input id="week_start" name="week_start" type="date" value="{{ $filters['week_start'] }}" :disabled="period !== 'weekly'" class="{{ $inputClass }}">
                </div>
                <div x-show="period === 'monthly'">
                    <label for="month" class="mb-1.5 block text-sm font-semibold text-ink">Bulan</label>
                    <input id="month" name="month" type="month" value="{{ $filters['month'] }}" :disabled="period !== 'monthly'" class="{{ $inputClass }}">
                </div>
                <div x-show="period === 'custom'">
                    <label for="start_date" class="mb-1.5 block text-sm font-semibold text-ink">Tanggal Mulai</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] }}" :disabled="period !== 'custom'" class="{{ $inputClass }}">
                </div>
                <div x-show="period === 'custom'">
                    <label for="end_date" class="mb-1.5 block text-sm font-semibold text-ink">Tanggal Akhir</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] }}" :disabled="period !== 'custom'" class="{{ $inputClass }}">
                </div>
            </div>

            <div>
                <label for="wallet_id" class="mb-1.5 block text-sm font-semibold text-ink">Dompet</label>
                <select id="wallet_id" name="wallet_id" class="{{ $inputClass }}">
                    <option value="all" @selected($filters['wallet_id'] === 'all')>Semua Dompet</option>
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected((string) $filters['wallet_id'] === (string) $wallet->id)>{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="category_filter" class="mb-1.5 block text-sm font-semibold text-ink">Kategori</label>
                <select id="category_filter" name="category_filter" class="{{ $inputClass }}">
                    <option value="all" @selected($filters['category_filter'] === 'all')>Semua Kategori</option>
                    <option value="type:income" @selected($filters['category_filter'] === 'type:income')>Semua Pemasukan</option>
                    <option value="type:expense" @selected($filters['category_filter'] === 'type:expense')>Semua Pengeluaran</option>
                    <optgroup label="Kategori Pemasukan">
                        @foreach ($categories->where('type', \App\Enums\TransactionType::Income) as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_filter'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Kategori Pengeluaran">
                        @foreach ($categories->where('type', \App\Enums\TransactionType::Expense) as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_filter'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:scale-95">
                <x-icon name="chart" size="h-4 w-4" />
                Tampilkan
            </button>
        </form>

        <p class="text-xs text-subtle">Periode aktif: <span class="font-medium text-ink">{{ $periodLabel }}</span></p>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                <p class="text-xs font-medium text-subtle">Total Pemasukan</p>
                <p class="mt-1 text-lg font-bold text-income">{{ $format($summary['income']) }}</p>
            </div>
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                <p class="text-xs font-medium text-subtle">Total Pengeluaran</p>
                <p class="mt-1 text-lg font-bold text-expense">{{ $format($summary['expense']) }}</p>
            </div>
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                <p class="text-xs font-medium text-subtle">Laba Bersih</p>
                <p class="mt-1 text-lg font-bold {{ $summary['net'] < 0 ? 'text-expense' : 'text-income' }}">{{ $format($summary['net']) }}</p>
            </div>
        </div>

        {{-- Grafik Tren Alur Kas --}}
        <section class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <x-icon name="chart" size="h-4 w-4" class="text-primary" />
                    Grafik Tren Alur Kas
                </h2>
                @if ($chart['count'] > 0)
                    <div class="flex items-center gap-1.5">
                        <button type="button" data-chart-type="auto"
                                class="rounded-lg bg-primary px-2.5 py-1.5 text-xs font-semibold text-white transition active:scale-95">Otomatis</button>
                        <button type="button" data-chart-type="line"
                                class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-medium text-subtle transition hover:bg-muted active:scale-95">Garis</button>
                        <button type="button" data-chart-type="bar"
                                class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-medium text-subtle transition hover:bg-muted active:scale-95">Batang</button>
                    </div>
                @endif
            </div>

            @if ($chart['count'] === 0)
                <x-empty-state
                    title="Belum Ada Data"
                    message="Belum ada data transaksi pada periode dan filter yang dipilih." />
            @else
                <div class="h-72 w-full">
                    <canvas id="cashflow-chart"></canvas>
                </div>
                <script type="application/json" id="cashflow-data">@json($chart)</script>
                @vite('resources/js/cashflow-chart.js')
            @endif
        </section>

        @if ($transactions->isEmpty())
            <x-empty-state
                title="Belum Ada Data"
                message="Belum ada data transaksi pada periode dan filter yang dipilih." />
        @else
            <div class="space-y-2.5">
                @foreach ($transactions as $transaction)
                    <x-transaction-item :transaction="$transaction" />
                @endforeach
            </div>
        @endif
    </div>
@endsection