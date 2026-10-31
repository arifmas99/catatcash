<?php

namespace App\Http\Middleware;

use App\Services\PinSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinIsUnlocked
{
    public function __construct(private readonly PinSecurityService $pin)
    {
    }

    /**
     * Wajibkan sesi PIN terbuka dan belum melewati idle timeout 15 menit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $unlockedAt = (int) $request->session()->get('pin_unlocked_at', 0);

        if (! $this->pin->isSessionUnlocked($unlockedAt)) {
            if ($unlockedAt > 0) {
                $request->session()->forget('pin_unlocked_at');

                return redirect()->route('pin.unlock')
                    ->withErrors(['pin' => 'Sesi terkunci karena tidak ada aktivitas 15 menit. Masukkan PIN kembali.']);
            }

            return redirect()->route('pin.unlock');
        }

        // Perpanjang jendela idle setiap kali ada aktivitas.
        $request->session()->put('pin_unlocked_at', time());

        return $next($request);
    }
}