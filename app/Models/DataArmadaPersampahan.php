<?php

namespace App\Models;

use App\Enums\KategoriArmadaPersampahan;
use Illuminate\Database\Eloquent\Model;

class DataArmadaPersampahan extends Model
{
    protected $table = 'data_armada_persampahan';

    protected $fillable = [
        'kategori',
        'daftar_armada',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => KategoriArmadaPersampahan::class,
            'daftar_armada' => 'array',
        ];
    }

    /**
     * Menghitung total unit armada terdaftar pada kategori ini.
     */
    public function totalUnit(): int
    {
        $items = $this->daftar_armada ?? [];
        if (! is_array($items)) {
            return 0;
        }

        return count(array_filter($items, function ($item) {
            return is_array($item) && filled($item['merk_type'] ?? null);
        }));
    }

    /**
     * Memastikan 4 kategori utama selalu tersedia di database.
     */
    public static function ensureCategoriesExist(): void
    {
        $categories = [
            KategoriArmadaPersampahan::RODA_2,
            KategoriArmadaPersampahan::RODA_4,
            KategoriArmadaPersampahan::RODA_6,
            KategoriArmadaPersampahan::ALAT_BERAT,
        ];

        foreach ($categories as $cat) {
            $val = $cat instanceof KategoriArmadaPersampahan ? $cat->value : $cat;
            self::firstOrCreate(
                ['kategori' => $val],
                ['daftar_armada' => []]
            );
        }
    }
}
