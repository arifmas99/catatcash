<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Hash;

class PinSecurityService
{
    /**
     * Jumlah maksimal percobaan PIN salah berturut-turut sebelum dikunci.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Durasi penguncian sementara dalam menit.
     */
    public const LOCK_MINUTES = 5;

    /**
     * Batas diam (idle) sesi terbuka sebelum terkunci lagi, dalam detik.
     */
    public const IDLE_TIMEOUT_SECONDS = 15 * 60;

    /**
     * Ambil satu-satunya baris pengaturan PIN, buat bila belum ada.
     */
    public function settings(): AppSetting
    {
        return AppSetting::current();
    }

    /**
     * Apakah PIN sudah pernah dibuat.
     */
    public function isConfigured(): bool
    {
        return filled($this->settings()->pin_hash);
    }

    /**
     * Simpan PIN baru sebagai hash dan bersihkan status percobaan.
     */
    public function setPin(string $pin): void
    {
        $settings = $this->settings();

        $settings->pin_hash = Hash::make($pin);
        $settings->failed_attempts = 0;
        $settings->locked_until = null;

        $settings->save();
    }

    /**
     * Sisa waktu penguncian dalam detik. 0 bila tidak sedang dikunci.
     */
    public function lockedSecondsRemaining(): int
    {
        $settings = $this->settings();

        if ($settings->locked_until === null) {
            return 0;
        }

        return max(0, $settings->locked_until->getTimestamp() - now()->getTimestamp());
    }

    /**
     * Apakah aplikasi sedang dalam masa kunci sementara.
     */
    public function isLocked(): bool
    {
        return $this->lockedSecondsRemaining() > 0;
    }

    /**
     * Periksa PIN. Mengembalikan status beserta sisa percobaan / sisa waktu kunci.
     *
     * @return array{status: string, attempts_left?: int, remaining?: int}
     */
    public function attempt(string $pin): array
    {
        $settings = $this->settings();

        // Masih dalam masa kunci: tolak tanpa memeriksa PIN.
        if ($this->isLocked()) {
            return [
                'status' => 'locked',
                'remaining' => $this->lockedSecondsRemaining(),
            ];
        }

        // Masa kunci sudah lewat: bersihkan status kunci sebelum memeriksa.
        if ($settings->locked_until !== null) {
            $settings->locked_until = null;
            $settings->failed_attempts = 0;
            $settings->save();
        }

        if (Hash::check($pin, $settings->pin_hash)) {
            $settings->failed_attempts = 0;
            $settings->locked_until = null;
            $settings->save();

            return ['status' => 'ok'];
        }

        $settings->failed_attempts = (int) $settings->failed_attempts + 1;

        if ($settings->failed_attempts >= self::MAX_ATTEMPTS) {
            $settings->locked_until = now()->addMinutes(self::LOCK_MINUTES);
            $settings->save();

            return [
                'status' => 'locked',
                'remaining' => $this->lockedSecondsRemaining(),
            ];
        }

        $settings->save();

        return [
            'status' => 'invalid',
            'attempts_left' => self::MAX_ATTEMPTS - $settings->failed_attempts,
        ];
    }

    /**
     * Apakah sesi terbuka masih dalam batas idle timeout.
     */
    public function isSessionUnlocked(int $unlockedAt): bool
    {
        if ($unlockedAt <= 0) {
            return false;
        }

        return (time() - $unlockedAt) <= self::IDLE_TIMEOUT_SECONDS;
    }
}