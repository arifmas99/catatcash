<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CatatCash') · CatatCash</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-app font-sans text-ink antialiased">
    {{-- Sidebar kiri: hanya tampil di desktop 1024px ke atas (DESIGN Bab 13). --}}
    <x-sidebar />

    {{-- Header mobile: sidebar & navigasi bawah disembunyikan di desktop. --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-line bg-surface px-4 py-3 lg:hidden">
        <span class="text-lg font-bold text-primary">CatatCash</span>
        <form method="POST" action="{{ route('pin.lock') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-line px-3 py-2 text-sm font-medium text-subtle transition hover:bg-muted active:scale-95">
                <x-icon name="lock" size="h-4 w-4" />
                Kunci
            </button>
        </form>
    </header>

    <div class="lg:pl-64">
        <main class="mx-auto w-full max-w-3xl px-4 py-6 pb-28 lg:max-w-5xl lg:px-8 lg:py-8 lg:pb-10">
            @yield('content')
        </main>
    </div>

    <x-bottom-nav />
</body>
</html>