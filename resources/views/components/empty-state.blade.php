@props(['title', 'message' => null, 'actionUrl' => null, 'actionLabel' => null])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line bg-surface px-6 py-10 text-center">
    <span class="grid h-12 w-12 place-items-center rounded-full bg-muted text-subtle">
        <x-icon name="inbox" size="h-6 w-6" />
    </span>
    <p class="mt-3 text-sm font-semibold text-ink">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-sm text-xs leading-relaxed text-subtle">{{ $message }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}"
           class="mt-4 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110 active:scale-95">
            {{ $actionLabel }}
        </a>
    @endif
</div>