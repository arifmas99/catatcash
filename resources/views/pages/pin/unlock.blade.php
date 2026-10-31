@extends('layouts.pin')

@section('title', 'Masukkan PIN')

@section('content')
    <div class="pin-header">
        <h1>Masukkan PIN</h1>
        <p>Aplikasi terkunci. Masukkan PIN 6 digit untuk membuka.</p>
    </div>
    <div class="pin-card">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any() && $lockedSeconds <= 0)
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pin.unlock.verify') }}" id="unlock-form" autocomplete="off">
            @csrf
            <input type="hidden" name="pin" id="pin-input" value="">

            <div class="dots" id="pin-dots" aria-hidden="true">
                @for ($i = 0; $i < 6; $i++)
                    <span class="dot"></span>
                @endfor
            </div>

            <div class="keypad" id="keypad" @if ($lockedSeconds > 0) aria-disabled="true" @endif>
                @foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9'] as $digit)
                    <button type="button" class="key" data-digit="{{ $digit }}">{{ $digit }}</button>
                @endforeach
                <span class="key-spacer" aria-hidden="true"></span>
                <button type="button" class="key" data-digit="0">0</button>
                <button type="button" class="key key-ghost" data-action="back">Hapus</button>
            </div>

            @if ($lockedSeconds > 0)
                <div class="lock-note" id="lock-note">
                    Terlalu banyak percobaan salah. Aplikasi dikunci sementara.
                    Coba lagi dalam <strong id="lock-timer">{{ gmdate('i:s', $lockedSeconds) }}</strong>.
                </div>
            @endif
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('unlock-form');
            const input = document.getElementById('pin-input');
            const dots = Array.from(document.querySelectorAll('#pin-dots .dot'));
            const keypad = document.getElementById('keypad');
            const locked = {{ $lockedSeconds > 0 ? 'true' : 'false' }};
            let value = '';

            function render() {
                dots.forEach(function (dot, index) {
                    dot.classList.toggle('filled', index < value.length);
                });
            }

            keypad.addEventListener('click', function (event) {
                if (locked) {
                    return;
                }

                const button = event.target.closest('button');
                if (!button) {
                    return;
                }

                if (button.dataset.digit !== undefined) {
                    if (value.length < 6) {
                        value += button.dataset.digit;
                        render();
                    }
                    if (value.length === 6) {
                        input.value = value;
                        form.submit();
                    }
                } else if (button.dataset.action === 'back') {
                    value = value.slice(0, -1);
                    render();
                }
            });

            const timer = document.getElementById('lock-timer');
            if (timer) {
                let remaining = {{ (int) $lockedSeconds }};
                const tick = setInterval(function () {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(tick);
                        window.location.reload();
                        return;
                    }
                    const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                    const seconds = String(remaining % 60).padStart(2, '0');
                    timer.textContent = minutes + ':' + seconds;
                }, 1000);
            }
        })();
    </script>
@endpush