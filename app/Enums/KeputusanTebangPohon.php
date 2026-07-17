<?php

namespace App\Enums;

enum KeputusanTebangPohon: string
{
    case DISETUJUI = 'Disetujui';
    case DITOLAK = 'Ditolak';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
