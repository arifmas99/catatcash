@extends('layouts.app')

@section('title', 'Aplikasi Terbuka')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl bg-gradient-to-br from-primary-light to-primary-dark p-6 text-white shadow-lg shadow-primary/20">
            <p class="text-sm text-white/80">Selamat datang</p>
            <h1 class="mt-1 text-2xl font-bold">Aplikasi Terbuka</h1>
            <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/80">
                Gerbang PIN sudah terbuka. Halaman ini adalah kerangka tampilan untuk menguji layout
                mobile dan desktop; halaman Beranda, Transaksi, Dompet, Kategori, dan Laporan dibuat
                pada tugas-tugas berikutnya.
            </p>

            <form method="POST" action="{{ route('pin.lock') }}" class="mt-5">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-white/90 active:scale-95">
                    <x-icon name="lock" size="h-4 w-4" />
                    Kunci Aplikasi
                </button>
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <x-icon name="plus" size="h-4 w-4" class="text-primary" />
                    Tampilan Mobile
                </div>
                <p class="mt-1 text-xs leading-relaxed text-subtle">
                    Navigasi bawah lima item dengan tombol + Catat berwarna ungu.
                </p>
            </div>
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <x-icon name="chart" size="h-4 w-4" class="text-primary" />
                    Tampilan Desktop
                </div>
                <p class="mt-1 text-xs leading-relaxed text-subtle">
                    Sidebar kiri tetap, dan navigasi bawah disembunyikan pada layar 1024px ke atas.
                </p>
            </div>
        </div>
    </div>
@endsection