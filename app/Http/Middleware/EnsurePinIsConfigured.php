<?php

namespace App\Http\Middleware;

use App\Services\PinSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinIsConfigured
{
    public function __construct(private readonly PinSecurityService $pin)
    {
    }

    /**
     * Jika PIN belum pernah dibuat, arahkan ke halaman Buat PIN.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->pin->isConfigured()) {
            return redirect()->route('pin.setup');
        }

        return $next($request);
    }
}