<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilDinas extends Model
{
    protected $table = 'profil_dinas';

    protected $fillable = [
        'visi',
        'misi',
        'tugas_fungsi',
        'visi_en',
        'misi_en',
        'tugas_fungsi_en',
        'struktur_organisasi_image',
        'pejabats',
    ];

    protected function casts(): array
    {
        return [
            'pejabats' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'visi' => '',
            'misi' => '',
            'tugas_fungsi' => '',
            'pejabats' => [],
        ]);
    }

    public function getVisiTranslatedAttribute(): string
    {
        if (app()->getLocale() === 'en' && !empty($this->visi_en)) {
            return $this->visi_en;
        }
        return $this->visi;
    }

    public function getMisiTranslatedAttribute(): string
    {
        if (app()->getLocale() === 'en' && !empty($this->misi_en)) {
            return $this->misi_en;
        }
        return $this->misi;
    }

    public function getTugasFungsiTranslatedAttribute(): string
    {
        if (app()->getLocale() === 'en' && !empty($this->tugas_fungsi_en)) {
            return $this->tugas_fungsi_en;
        }
        return $this->tugas_fungsi;
    }
}
