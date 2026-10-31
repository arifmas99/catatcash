@php
    $items = [
        ['label' => 'Beranda', 'route' => 'dashboard.index', 'icon' => 'home'],
        ['label' => 'Transaksi', 'route' => 'transactions.index', 'icon' => 'list'],
        ['label' => 'Catat', 'route' => 'transactions.create', 'icon' => 'plus', 'primary' => true],
        ['label' => 'Dompet', 'route' => 'wallets.index', 'icon' => 'wallet'],
        ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'chart'],
    ];
    $current = \Illuminate\Support\Facades\Route::currentRouteName();
    $link = fn (string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
@endphp

{{-- Navigasi bawah: hanya di mobile; disembunyikan di desktop (DESIGN Bab 13). --}}
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface lg:hidden" aria-label="Navigasi utama">
    <ul class="mx-auto grid max-w-3xl grid-cols-5 items-end gap-1 px-2 pb-2 pt-2">
        @foreach ($items as $item)
            @php $active = $current === $item['route']; @endphp
            @if (! empty($item['primary']))
                <li class="flex justify-center">
                    <a href="{{ $link($item['route']) }}"
                       aria-label="Catat Transaksi"
                       class="-mt-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-white shadow-lg shadow-primary/30 ring-4 ring-surface transition hover:brightness-110 active:scale-90">
                        <x-icon name="plus" size="h-6 w-6" />
                    </a>
                </li>
            @else
                <li>
                    <a href="{{ $link($item['route']) }}"
                       @if ($active) aria-current="page" @endif
                       class="flex flex-col items-center gap-1 rounded-xl px-1 py-1.5 text-[11px] font-medium transition active:scale-90 {{ $active ? 'text-primary' : 'text-subtle hover:text-ink' }}">
                        <x-icon :name="$item['icon']" size="h-5 w-5" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>