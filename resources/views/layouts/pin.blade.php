<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CatatCash') · CatatCash</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-app px-4 py-8 font-sans text-ink antialiased">
    <main class="w-full max-w-md">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>