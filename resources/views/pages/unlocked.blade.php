@extends('layouts.pin')

@section('title', 'Aplikasi Terbuka')

@section('content')
    <div class="pin-header">
        <h1>Aplikasi Terbuka</h1>
        <p>PIN berhasil dibuka.</p>
    </div>
    <div class="pin-card">
        <p class="muted">
            Gerbang PIN sudah terbuka. Halaman Beranda, Transaksi, Dompet, Kategori, dan Laporan
            akan dibuat pada tugas-tugas berikutnya.
        </p>

        <form method="POST" action="{{ route('pin.lock') }}" style="margin-top: 16px;">
            @csrf
            <button type="submit" class="btn-primary">Kunci Aplikasi</button>
        </form>
    </div>
@endsection