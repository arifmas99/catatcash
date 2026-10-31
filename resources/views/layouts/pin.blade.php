<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CatatCash') · CatatCash</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary-purple: #6D28D9;
            --color-primary-gradient: linear-gradient(135deg, #7C3AED 0%, #4C1D95 100%);
            --color-bg-app: #F8FAFC;
            --color-surface-card: #FFFFFF;
            --color-text-primary: #0F172A;
            --color-text-secondary: #64748B;
            --color-border: #E2E8F0;
            --color-income: #16A34A;
            --color-income-soft: #DCFCE7;
            --color-expense: #DC2626;
            --color-expense-soft: #FEE2E2;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--color-bg-app);
            color: var(--color-text-primary);
        }
        .pin-shell { width: 100%; max-width: 420px; }
        .pin-header {
            background: var(--color-primary-gradient);
            color: #fff;
            border-radius: 20px 20px 0 0;
            padding: 28px 24px;
            text-align: center;
        }
        .pin-header h1 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .pin-header p { margin: 0; font-size: 14px; opacity: .85; }
        .pin-card {
            background: var(--color-surface-card);
            border: 1px solid var(--color-border);
            border-top: 0;
            border-radius: 0 0 20px 20px;
            padding: 24px;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, .06), 0 1px 2px -1px rgba(15, 23, 42, .06);
        }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .field input {
            width: 100%;
            padding: 12px 14px;
            font-size: 18px;
            letter-spacing: .4em;
            text-align: center;
            color: var(--color-text-primary);
            background: #fff;
            border: 1px solid var(--color-border);
            border-radius: 12px;
        }
        .field input:focus { outline: 2px solid var(--color-primary-purple); border-color: transparent; }
        .btn-primary {
            display: block;
            width: 100%;
            padding: 13px 16px;
            border: 0;
            border-radius: 12px;
            background: var(--color-primary-purple);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
        }
        .btn-primary:hover { filter: brightness(1.06); }
        .btn-primary:active { transform: translateY(1px); }
        .help { margin: 12px 0 0; font-size: 12px; color: var(--color-text-secondary); }
        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 14px;
            background: var(--color-expense-soft);
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }
        .alert-success {
            background: var(--color-income-soft);
            color: #166534;
            border-color: #86EFAC;
        }
        .dots { display: flex; justify-content: center; gap: 14px; margin: 4px 0 24px; }
        .dot {
            width: 16px;
            height: 16px;
            border-radius: 9999px;
            border: 2px solid var(--color-primary-purple);
            background: transparent;
            transition: background .12s ease;
        }
        .dot.filled { background: var(--color-primary-purple); }
        .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .key {
            padding: 16px 0;
            font-size: 22px;
            font-weight: 600;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            background: #fff;
            color: var(--color-text-primary);
            cursor: pointer;
            user-select: none;
        }
        .key:active { background: #F1F5F9; }
        .key-ghost { font-size: 15px; color: var(--color-text-secondary); }
        .key-spacer { visibility: hidden; }
        .keypad[aria-disabled="true"] .key { opacity: .4; pointer-events: none; }
        .lock-note {
            margin-top: 16px;
            text-align: center;
            font-size: 14px;
            color: var(--color-expense);
        }
        .lock-note strong { font-variant-numeric: tabular-nums; }
        .muted { color: var(--color-text-secondary); font-size: 14px; line-height: 1.6; }
    </style>
</head>
<body>
    <main class="pin-shell">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>