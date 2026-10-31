@php
    $items = [
        ['label' => 'Beranda', 'route' => 'dashboard.index', 'icon' => 'home'],
        ['label' => 'Transaksi', 'route' => 'transactions.index', 'icon' => 'list'],
        ['label' => 'Catat Transaksi', 'route' => 'transactions.create', 'icon' => 'plus'],
        ['label' => 'Dompet & Kategori', 'route' => 'wallets.index', 'icon' => 'wallet'],
        ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'chart'],
    ];
    $current = \Illuminate\Support\Facades\Route::currentRouteName();
    $link = fn (string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
@endphp

<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-line bg-surface lg:flex">
    <div class="flex items-center gap-2 border-b border-line px-6 py-5">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary text-white">
            <x-icon name="wallet" size="h-5 w-5" />
        </span>
        <span class="text-lg font-bold text-ink">CatatCash</span>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi utama">
        @foreach ($items as $item)
            @php $active = $current === $item['route']; @endphp
            <a href="{{ $link($item['route']) }}"
               @if ($active) aria-current="page" @endif
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition active:scale-[0.98] {{ $active ? 'bg-primary/10 text-primary' : 'text-subtle hover:bg-muted hover:text-ink' }}">
                <span class="grid h-8 w-8 place-items-center rounded-lg transition {{ $active ? 'bg-primary text-white' : 'bg-muted text-subtle group-hover:text-ink' }}">
                    <x-icon :name="$item['icon']" size="h-4 w-4" />
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="border-t border-line p-3">
        <form method="POST" action="{{ route('pin.lock') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-expense transition hover:bg-expense-soft active:scale-[0.98]">
                <x-icon name="lock" size="h-5 w-5" />
                Kunci Aplikasi
            </button>
        </form>
    </div>
</aside>