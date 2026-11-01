@extends('layouts.app')

@section('title', 'Kelola Kategori')

@section('content')
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                    <x-icon name="tag" size="h-5 w-5" />
                </span>
                <div>
                    <h1 class="text-lg font-semibold text-ink">Kelola Kategori</h1>
                    <p class="text-xs text-subtle">Kategori dipisah per jenis: Pemasukan dan Pengeluaran.</p>
                </div>
            </div>
            <a href="{{ route('wallets.index') }}"
               class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-line px-3 py-2 text-xs font-medium text-subtle transition hover:bg-muted hover:text-primary active:scale-95">
                <x-icon name="wallet" size="h-4 w-4" />
                Dompet
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-income-soft px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        {{-- Tab jenis kategori --}}
        <div class="flex gap-2">
            <a href="{{ route('categories.index', ['type' => 'income']) }}"
               class="flex-1 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition active:scale-95 {{ $activeType === 'income' ? 'bg-primary text-white shadow-sm shadow-primary/20' : 'border border-line bg-surface text-subtle hover:bg-muted' }}">
                Pemasukan ({{ $counts['income'] }})
            </a>
            <a href="{{ route('categories.index', ['type' => 'expense']) }}"
               class="flex-1 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition active:scale-95 {{ $activeType === 'expense' ? 'bg-primary text-white shadow-sm shadow-primary/20' : 'border border-line bg-surface text-subtle hover:bg-muted' }}">
                Pengeluaran ({{ $counts['expense'] }})
            </a>
        </div>

        {{-- Tambah kategori --}}
        <section class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink">
                <x-icon name="plus" size="h-4 w-4" class="text-primary" />
                Tambah Kategori {{ $activeType === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
            </h2>
            <form method="POST" action="{{ route('categories.store') }}" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="hidden" name="type" value="{{ $activeType }}">
                <input type="text" name="name" maxlength="100" required placeholder="Nama kategori baru"
                       class="min-w-0 flex-1 rounded-xl border border-line px-3 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:scale-95">
                    <x-icon name="plus" size="h-4 w-4" />
                    Tambah
                </button>
            </form>
        </section>

        {{-- Daftar kategori --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <x-icon :name="$activeType === 'income' ? 'arrow-up' : 'arrow-down'" size="h-4 w-4" :class="$activeType === 'income' ? 'text-income' : 'text-expense'" />
                    Daftar Kategori {{ $activeType === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                </h2>
                <span class="text-xs text-subtle">{{ $categories->count() }} kategori</span>
            </div>

            @if ($categories->isEmpty())
                <x-empty-state title="Belum Ada Kategori" message="Tambahkan kategori pertama untuk jenis ini." />
            @else
                @foreach ($categories as $item)
                    @php
                        $category = $item['category'];
                        $isIncome = $category->type === \App\Enums\TransactionType::Income;
                        $icon = \App\Support\CategoryIconResolver::resolve($category->name, $category->type);
                    @endphp
                    <div x-data="{ editing: false }" class="space-y-2">
                        <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm transition hover:shadow-md">
                            <div class="flex items-center gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $isIncome ? 'bg-income-soft text-income' : 'bg-expense-soft text-expense' }}">
                                    <x-icon :name="$icon" size="h-5 w-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $category->name }}</p>
                                    <p class="text-xs text-subtle">{{ $item['transaction_count'] }} transaksi</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="editing = !editing"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-subtle transition hover:bg-muted hover:text-primary active:scale-95">
                                        <x-icon name="edit" size="h-3.5 w-3.5" />
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                          onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                @disabled(! $item['can_delete'])
                                                title="{{ $item['delete_reason'] }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $item['can_delete'] ? 'border-expense/30 text-expense hover:bg-expense-soft active:scale-95' : 'cursor-not-allowed border-line text-slate-300' }}">
                                            <x-icon name="trash" size="h-3.5 w-3.5" />
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                @if (! $item['can_delete'])
                                    <p class="text-[11px] text-subtle">{{ $item['delete_reason'] }}</p>
                                @endif
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('categories.update', $category) }}"
                              class="flex flex-col gap-2 rounded-2xl border border-line bg-surface p-3 shadow-sm sm:flex-row sm:items-center">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" maxlength="100" required
                                   class="min-w-0 flex-1 rounded-xl border border-line px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-primary/40">
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110 active:scale-95">
                                    <x-icon name="check" size="h-3.5 w-3.5" />
                                    Simpan
                                </button>
                                <button type="button" @click="editing = false"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-line px-3 py-2 text-xs font-medium text-subtle transition hover:bg-muted active:scale-95">
                                    <x-icon name="x" size="h-3.5 w-3.5" />
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            @endif
        </section>
    </div>
@endsection