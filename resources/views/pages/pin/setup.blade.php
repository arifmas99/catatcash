@extends('layouts.pin')

@section('title', 'Buat PIN')

@section('content')
    <div class="pin-header">
        <h1>Buat PIN CatatCash</h1>
        <p>Buat PIN 6 digit untuk mengunci aplikasi.</p>
    </div>
    <div class="pin-card">
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pin.setup.store') }}" autocomplete="off">
            @csrf
            <div class="field">
                <label for="pin">PIN Baru (6 digit)</label>
                <input id="pin" name="pin" type="password" inputmode="numeric" pattern="\d{6}"
                       maxlength="6" required autofocus>
            </div>
            <div class="field">
                <label for="pin_confirmation">Konfirmasi PIN</label>
                <input id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric"
                       pattern="\d{6}" maxlength="6" required>
            </div>
            <button type="submit" class="btn-primary">Simpan PIN</button>
        </form>

        <p class="help">PIN disimpan dalam bentuk hash, tidak pernah sebagai angka polos.</p>
    </div>
@endsection