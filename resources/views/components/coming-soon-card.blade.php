@props(['title', 'description' => null])

{{-- Elemen statis, tidak bisa diklik (DESIGN Bab 4.5 & 8). --}}
<div class="pointer-events-none cursor-default select-none rounded-2xl border border-line bg-muted p-4">
    <div class="flex items-center justify-between gap-2">
        <p class="text-sm font-semibold text-subtle">{{ $title }}</p>
        <span class="inline-flex items-center gap-1 rounded-full bg-surface px-2 py-0.5 text-[11px] font-medium text-subtle">
            <x-icon name="sparkles" size="h-3 w-3" />
            Segera Hadir
        </span>
    </div>
    @if ($description)
        <p class="mt-1 text-xs text-subtle">{{ $description }}</p>
    @endif
</div>