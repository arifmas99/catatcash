<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupPinRequest;
use App\Http\Requests\UnlockPinRequest;
use App\Services\PinSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PinController extends Controller
{
    public function __construct(private readonly PinSecurityService $pin)
    {
    }

    /**
     * Halaman Buat PIN: hanya tampil bila PIN belum pernah dibuat.
     */
    public function showSetupForm(): RedirectResponse|View
    {
        if ($this->pin->isConfigured()) {
            return redirect()->route('pin.unlock');
        }

        return view('pages.pin.setup');
    }

    /**
     * Simpan PIN pertama kali sebagai hash, lalu buka sesi.
     */
    public function storeSetup(SetupPinRequest $request): RedirectResponse
    {
        if ($this->pin->isConfigured()) {
            return redirect()->route('pin.unlock');
        }

        $this->pin->setPin($request->input('pin'));

        $request->session()->regenerate();
        $request->session()->put('pin_unlocked_at', time());

        return redirect()->route('dashboard.index');
    }

    /**
     * Layar Masukkan PIN.
     */
    public function showUnlockForm(Request $request): RedirectResponse|View
    {
        $unlockedAt = (int) $request->session()->get('pin_unlocked_at', 0);

        if ($this->pin->isSessionUnlocked($unlockedAt)) {
            return redirect()->route('dashboard.index');
        }

        return view('pages.pin.unlock', [
            'lockedSeconds' => $this->pin->lockedSecondsRemaining(),
        ]);
    }

    /**
     * Verifikasi PIN beserta aturan lockout 5 kali salah / 5 menit.
     */
    public function verifyUnlock(UnlockPinRequest $request): RedirectResponse
    {
        $result = $this->pin->attempt($request->input('pin'));

        if ($result['status'] === 'ok') {
            $request->session()->regenerate();
            $request->session()->put('pin_unlocked_at', time());

            return redirect()->intended(route('dashboard.index'));
        }

        if ($result['status'] === 'locked') {
            return redirect()->route('pin.unlock')->withErrors([
                'pin' => 'Terlalu banyak percobaan salah. Aplikasi dikunci sementara, silakan coba lagi dalam '
                    .$this->formatDuration($result['remaining']).'.',
            ]);
        }

        return redirect()->route('pin.unlock')->withErrors([
            'pin' => 'PIN salah. Sisa percobaan: '.$result['attempts_left'].' kali.',
        ]);
    }

    /**
     * Kunci Aplikasi: hapus status terbuka di sesi.
     */
    public function lockSession(Request $request): RedirectResponse
    {
        $request->session()->forget('pin_unlocked_at');

        return redirect()->route('pin.unlock');
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0 && $remainingSeconds > 0) {
            return $minutes.' menit '.$remainingSeconds.' detik';
        }

        if ($minutes > 0) {
            return $minutes.' menit';
        }

        return $remainingSeconds.' detik';
    }
}