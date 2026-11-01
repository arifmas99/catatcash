<?php

namespace App\Support;

use App\Enums\TransactionType;

/**
 * Pemetaan ikon kategori dikunci oleh JENIS + NAMA (tanpa kolom ikon di tabel).
 *
 * Kategori bawaan dari seeder memakai ikonnya masing-masing (kunci "jenis|nama").
 * Kategori lain (baru atau yang namanya diubah) yang tidak cocok dengan kunci
 * jenis + nama otomatis memakai ikon jenisnya: panah hijau (pemasukan) atau
 * panah merah (pengeluaran). "Lainnya" tetap ellipsis, warnanya ikut jenis.
 */
class CategoryIconResolver
{
    /**
     * @var array<string, string>
     */
    private const ICONS = [
        'income|Gaji & Bonus' => 'banknotes',
        'income|Modal Masuk' => 'bank',
        'income|Penjualan' => 'tag',
        'income|Uang Saku' => 'wallet',
        'income|Lainnya' => 'ellipsis',
        'expense|Belanja & Hiburan' => 'ticket',
        'expense|Belanja Barang/Jasa' => 'shopping-bag',
        'expense|Gaji & Upah' => 'briefcase',
        'expense|Makan & Minum' => 'mug',
        'expense|Sosial' => 'users',
        'expense|Lainnya' => 'ellipsis',
    ];

    public static function resolve(string $name, TransactionType|string $type): string
    {
        $typeValue = $type instanceof TransactionType ? $type->value : $type;

        return self::ICONS[$typeValue.'|'.$name]
            ?? ($typeValue === TransactionType::Income->value ? 'arrow-up' : 'arrow-down');
    }
}