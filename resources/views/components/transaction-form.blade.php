@props([
    'action',
    'method' => 'POST',
    'transaction' => null,
    'wallets',
    'categoryOptions',
])

@php
    $initialType = old('type', $transaction?->type?->value ?? 'income');
    $initialAmount = old('amount', $transaction?->amount);
    $initialWallet = old('wallet_id', $transaction?->wallet_id ?? $wallets->first()?->id);
    $initialCategory = old('category_id', $transaction?->category_id);
    $initialDate = old('transaction_date', $transaction?->transaction_date?->toDateString() ?? now()->toDateString());
    $initialTime = old('transaction_time', $transaction?->transaction_time ? substr((string) $transaction->transaction_time, 0, 5) : now()->format('H:i'));
    $initialNotes = old('notes', $transaction?->notes);
    $inputClass = 'w-full rounded-xl border border-line px-3 py-2.5 text-sm text-ink placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40';
@endphp

<div x-data="{ type: '{{ $initialType }}' }" class="rounded-2xl border border-line bg-surface p-4 shadow-sm sm:p-6">
    <form method="POST" action="{{ $action }}" class="space-y-4">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <input type="hidden" name="type" :value="type">

        {{-- Jenis transaksi (toggle mengubah daftar kategori) --}}
        <div>
            <span class="mb-2 block text-sm font-semibold text-ink">Jenis Transaksi</span>
            <div class="flex gap-2">
                <button type="button" @click="type = 'income'"
                        :class="type === 'income' ? 'bg-income text-white shadow-sm' : 'border border-line text-subtle hover:bg-muted'"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition active:scale-95">
                    <x-icon name="arrow-up" size="h-4 w-4" />
                    Pemasukan
                </button>
                <button type="button" @click="type = 'expense'"
                        :class="type === 'expense' ? 'bg-expense text-white shadow-sm' : 'border border-line text-subtle hover:bg-muted'"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition active:scale-95">
                    <x-icon name="arrow-down" size="h-4 w-4" />
                    Pengeluaran
                </button>
            </div>
        </div>

        {{-- Nominal --}}
        <div>
            <label for="amount" class="mb-1.5 block text-sm font-semibold text-ink">Nominal (Rp)</label>
            <input id="amount" name="amount" type="number" min="1" step="1" inputmode="numeric" required
                   value="{{ $initialAmount }}" placeholder="Contoh: 50000" class="{{ $inputClass }}">
        </div>

        {{-- Tanggal & jam --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="transaction_date" class="mb-1.5 block text-sm font-semibold text-ink">Tanggal</label>
                <input id="transaction_date" name="transaction_date" type="date" required
                       value="{{ $initialDate }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="transaction_time" class="mb-1.5 block text-sm font-semibold text-ink">Jam</label>
                <input id="transaction_time" name="transaction_time" type="time" required
                       value="{{ $initialTime }}" class="{{ $inputClass }}">
            </div>
        </div>

        {{-- Dompet --}}
        <div>
            <label for="wallet_id" class="mb-1.5 block text-sm font-semibold text-ink">Dompet</label>
            <select id="wallet_id" name="wallet_id" required class="{{ $inputClass }}">
                @foreach ($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected((string) $initialWallet === (string) $wallet->id)>{{ $wallet->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Kategori: hanya daftar sesuai jenis yang aktif --}}
        <div>
            <label for="category_income" class="mb-1.5 block text-sm font-semibold text-ink">Kategori</label>
            <select id="category_income" name="category_id" x-show="type === 'income'"
                    :disabled="type !== 'income'" :required="type === 'income'" class="{{ $inputClass }}">
                @foreach ($categoryOptions['income'] as $category)
                    <option value="{{ $category->id }}" @selected((string) $initialCategory === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="category_expense" name="category_id" x-show="type === 'expense'" x-cloak
                    :disabled="type !== 'expense'" :required="type === 'expense'" class="{{ $inputClass }}">
                @foreach ($categoryOptions['expense'] as $category)
                    <option value="{{ $category->id }}" @selected((string) $initialCategory === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Catatan opsional --}}
        <div>
            <label for="notes" class="mb-1.5 block text-sm font-semibold text-ink">Catatan (opsional)</label>
            <textarea id="notes" name="notes" rows="2" maxlength="2000" placeholder="Catatan tambahan"
                      class="{{ $inputClass }}">{{ $initialNotes }}</textarea>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="flex items-center gap-2 pt-1">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:scale-95">
                <x-icon name="check" size="h-4 w-4" />
                Simpan
            </button>
            <a href="{{ route('transactions.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-subtle transition hover:bg-muted active:scale-95">
                Batal
            </a>
        </div>
    </form>
</div>