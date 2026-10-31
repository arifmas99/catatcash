@props([
    'formAction',
    'title',
    'subtitle' => null,
    'isLocked' => false,
    'remainingSeconds' => 0,
    'mode' => 'single',
])

@php
    $mode = $mode === 'confirm' ? 'confirm' : 'single';
    $locked = (bool) $isLocked;
    $remaining = (int) $remainingSeconds;
@endphp

<div class="w-full max-w-md"
     x-data="pinKeypad({ mode: '{{ $mode }}', locked: {{ $locked ? 'true' : 'false' }}, remaining: {{ $remaining }} })">
    <div class="rounded-t-2xl bg-gradient-to-br from-primary-light to-primary-dark px-6 py-7 text-center text-white">
        <h1 class="text-xl font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-white/80">{{ $subtitle }}</p>
        @endif
        @if ($mode === 'confirm')
            <p class="mt-2 text-xs font-medium text-white/70" x-text="stepLabel" x-cloak></p>
        @endif
    </div>

    <div class="rounded-b-2xl border border-t-0 border-line bg-surface p-6 shadow-sm">
        {{ $slot }}

        <form x-ref="form" method="POST" action="{{ $formAction }}" autocomplete="off">
            @csrf
            <input type="hidden" name="pin" x-ref="pin" value="">
            @if ($mode === 'confirm')
                <input type="hidden" name="pin_confirmation" x-ref="confirm" value="">
            @endif

            <div class="mb-6 flex justify-center gap-3">
                <template x-for="index in 6" :key="index">
                    <span class="h-4 w-4 rounded-full border-2 border-primary transition-colors"
                          :class="index <= value.length ? 'bg-primary' : 'bg-transparent'"></span>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-3" :class="locked ? 'pointer-events-none opacity-40' : ''">
                <template x-for="digit in ['1','2','3','4','5','6','7','8','9']" :key="digit">
                    <button type="button" @click="press(digit)"
                            class="rounded-xl border border-line bg-surface py-4 text-2xl font-semibold text-ink transition hover:bg-muted active:scale-95 active:bg-muted"
                            x-text="digit"></button>
                </template>
                <span aria-hidden="true"></span>
                <button type="button" @click="press('0')"
                        class="rounded-xl border border-line bg-surface py-4 text-2xl font-semibold text-ink transition hover:bg-muted active:scale-95 active:bg-muted">0</button>
                <button type="button" @click="backspace()"
                        class="inline-flex flex-col items-center justify-center gap-0.5 rounded-xl py-3 text-xs font-medium text-subtle transition hover:bg-muted active:scale-95">
                    <x-icon name="backspace" size="h-5 w-5" />
                    Hapus
                </button>
            </div>

            <div x-show="locked && remaining > 0" x-cloak class="mt-4 text-center text-sm text-expense">
                Terlalu banyak percobaan salah. Aplikasi dikunci sementara.
                Coba lagi dalam <strong class="tabular-nums" x-text="lockedLabel"></strong>.
            </div>
        </form>
    </div>
</div>