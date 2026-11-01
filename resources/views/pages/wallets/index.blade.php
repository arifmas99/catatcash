@extends('layouts.app')

@section('title', 'Kelola Dompet')

@section('content')
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                    <x-icon name="wallet" size="h-5 w-5" />
                </span>
                <div>
                    <h1 class="text-lg font-semibold text-ink">Kelola Dompet</h1>
                    <p class="text-xs text-subtle">Tambah, ubah nama, atau hapus dompet.</p>
                </div>
            </div>
            <a href="{{ route('categories.index') }}"
               class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-line px-3 py-2 text-xs font-medium text-subtle transition hover:bg-muted hover:text-primary active:scale-95">
                <x-icon name="tag" size="h-4 w-4" />
                Kategori
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-income-soft px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Tambah dompet --}}
        <section class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink">
                <x-icon name="plus" size="h-4 w-4" class="text-primary" />
                Tambah Dompet
            </h2>
            <form method="POST" action="{{ route('wallets.store') }}" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="text" name="name" maxlength="100" required placeholder="Nama dompet baru"
                       class="min-w-0 flex-1 rounded-xl border border-line px-3 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:scale-95">
                    <x-icon name="plus" size="h-4 w-4" />
                    Tambah
                </button>
            </form>
        </section>

        {{-- Daftar dompet --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <x-icon name="wallet" size="h-4 w-4" class="text-primary" />
                    Daftar Dompet
                </h2>
                <span class="text-xs text-subtle">{{ $walletCount }} dompet</span>
            </div>

            @if ($walletBalances->isEmpty())
                <x-empty-state title="Belum Ada Dompet" message="Tambahkan dompet pertama Anda di atas." />
            @else
                @foreach ($walletBalances as $item)
                    @php $wallet = $item['wallet']; @endphp
                    <div x-data="{ editing: false }" class="space-y-2">
                        <x-wallet-card
                            :wallet-name="$wallet->name"
                            :calculated-balance="$item['balance']"
                            :is-primary="$wallet->is_primary"
                            :transaction-count="$item['transaction_count']">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="editing = !editing"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-subtle transition hover:bg-muted hover:text-primary active:scale-95">
                                        <x-icon name="edit" size="h-3.5 w-3.5" />
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                                          onsubmit="return confirm('Hapus dompet ini?')">
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
                        </x-wallet-card>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('wallets.update', $wallet) }}"
                              class="flex flex-col gap-2 rounded-2xl border border-line bg-surface p-3 shadow-sm sm:flex-row sm:items-center">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $wallet->name }}" maxlength="100" required
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