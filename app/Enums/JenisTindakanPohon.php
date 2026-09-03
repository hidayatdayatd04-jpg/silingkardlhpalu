<?php

namespace App\Enums;

enum JenisTindakanPohon: string
{
    case PENEBANGAN = 'Penebangan';
    case PEMANGKASAN = 'Pemangkasan';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::PENEBANGAN => 'danger',
            self::PEMANGKASAN => 'warning',
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
