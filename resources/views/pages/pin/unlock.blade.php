@extends('layouts.pin')

@section('title', 'Masukkan PIN')

@section('content')
    <x-pin-keypad
        :form-action="route('pin.unlock.verify')"
        title="Masukkan PIN"
        subtitle="Aplikasi terkunci. Masukkan PIN 6 digit untuk membuka."
        :is-locked="$lockedSeconds > 0"
        :remaining-seconds="$lockedSeconds"
        mode="single">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-green-200 bg-income-soft px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any() && $lockedSeconds <= 0)
            <div class="mb-4 rounded-xl border border-expense/30 bg-expense-soft px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif
    </x-pin-keypad>
@endsection