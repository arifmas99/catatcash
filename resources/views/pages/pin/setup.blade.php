@extends('layouts.pin')

@section('title', 'Buat PIN')

@section('content')
    <x-pin-keypad
        :form-action="route('pin.setup.store')"
        title="Buat PIN CatatCash"
        subtitle="PIN 6 digit untuk mengunci aplikasi."
        mode="confirm">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="mt-4 text-center text-xs text-subtle">PIN disimpan dalam bentuk hash, tidak pernah sebagai angka polos.</p>
    </x-pin-keypad>
@endsection