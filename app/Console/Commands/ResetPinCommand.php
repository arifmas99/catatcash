<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetPinCommand extends Command
{
    protected $signature = 'catatcash:reset-pin
                            {--force : Jalankan reset tanpa konfirmasi interaktif (untuk skrip/pengujian).}';

    protected $description = 'Reset PIN CatatCash, kunci ulang aplikasi, dan bersihkan semua sesi aktif. Tanpa --force tetap meminta konfirmasi; dengan --force langsung dijalankan.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin mereset PIN CatatCash?', false)) {
            $this->warn('Dibatalkan. PIN tidak diubah.');

            return self::SUCCESS;
        }

        $settings = AppSetting::current();
        $settings->pin_hash = null;
        $settings->failed_attempts = 0;
        $settings->locked_until = null;
        $settings->save();

        $cleared = $this->clearAllSessions();

        $this->info('PIN CatatCash berhasil direset.');
        $this->line('Semua sesi aktif dibersihkan ('.$cleared.' baris sesi).');
        $this->line('Buka aplikasi di browser untuk membuat PIN baru.');

        return self::SUCCESS;
    }

    /**
     * Bersihkan seluruh sesi aktif di server sesuai driver sesi yang dipakai.
     */
    private function clearAllSessions(): int
    {
        $driver = config('session.driver');

        if ($driver === 'database') {
            return DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->delete();
        }

        if ($driver === 'file') {
            $files = File::glob(storage_path('framework/sessions').'/*');
            File::delete($files);

            return count($files);
        }

        return 0;
    }
}