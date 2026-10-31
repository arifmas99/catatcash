<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pin_hash', 'failed_attempts', 'locked_until'])]
class AppSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_attempts' => 'integer',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Ambil baris pengaturan tunggal, buat bila belum ada.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
