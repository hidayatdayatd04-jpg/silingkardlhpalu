<?php

namespace Illuminate\Support;

use Illuminate\Support\Traits\Macroable;

/**
 * Fallback for Illuminate\Support\Number when the PHP intl extension is unavailable.
 */
class Number
{
    use Macroable;

    protected static string $locale = 'en';

    protected static string $currency = 'USD';

    public static function format(int|float $number, ?int $precision = null, ?int $maxPrecision = null, ?string $locale = null): string
    {
        $decimals = $maxPrecision ?? $precision ?? 0;

        return self::formatWithNative($number, $decimals, $locale ?? static::$locale);
    }

    public static function parse(string $string, ?int $type = null, ?string $locale = null): int|float|false
    {
        $normalized = str_replace(['.', ' '], '', $string);
        $normalized = str_replace(',', '.', $normalized);

        if ($type === 1) {
            return filter_var($normalized, FILTER_VALIDATE_INT);
        }

        return filter_var($normalized, FILTER_VALIDATE_FLOAT);
    }

    public static function parseInt(string $string, ?string $locale = null): int|false
    {
        return self::parse($string, 1, $locale);
    }

    public static function parseFloat(string $string, ?string $locale = null): float|false
    {
        return self::parse($string, 2, $locale);
    }

    public static function spell(int|float $number, ?string $locale = null, ?int $after = null, ?int $until = null): string
    {
        if (! is_null($after) && $number <= $after) {
            return (string) static::format($number, locale: $locale);
        }

        if (! is_null($until) && $number >= $until) {
            return (string) static::format($number, locale: $locale);
        }

        return (string) static::format($number, locale: $locale);
    }

    public static function ordinal(int|float $number, ?string $locale = null): string
    {
        return (string) static::format($number, locale: $locale);
    }

    public static function spellOrdinal(int|float $number, ?string $locale = null): string
    {
        return (string) static::format($number, locale: $locale);
    }

    public static function percentage(int|float $number, int $precision = 0, ?int $maxPrecision = null, ?string $locale = null): string
    {
        $decimals = $maxPrecision ?? $precision;

        return static::format($number, $decimals, $maxPrecision, $locale).'%';
    }

    public static function currency(int|float $number, string $in = '', ?string $locale = null, ?int $precision = null): string
    {
        $currency = ! empty($in) ? $in : static::$currency;
        $decimals = $precision ?? 2;
        $formatted = static::format($number, $decimals, null, $locale);

        return trim($currency.' '.$formatted);
    }

    public static function fileSize(int|float $bytes, int $precision = 0, ?int $maxPrecision = null): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $unitCount = count($units);

        for ($i = 0; (abs($bytes) / 1024) > 0.9 && ($i < $unitCount - 1); $i++) {
            $bytes /= 1024;
        }

        return sprintf('%s %s', static::format($bytes, $precision, $maxPrecision), $units[$i]);
    }

    public static function abbreviate(int|float $number, int $precision = 0, ?int $maxPrecision = null): string
    {
        return static::forHumans($number, $precision, $maxPrecision, abbreviate: true);
    }

    public static function forHumans(int|float $number, int $precision = 0, ?int $maxPrecision = null, bool $abbreviate = false): string
    {
        return static::summarize($number, $precision, $maxPrecision, $abbreviate ? [
            3 => 'K',
            6 => 'M',
            9 => 'B',
            12 => 'T',
            15 => 'Q',
        ] : [
            3 => ' thousand',
            6 => ' million',
            9 => ' billion',
            12 => ' trillion',
            15 => ' quadrillion',
        ]);
    }

    protected static function summarize(int|float $number, int $precision = 0, ?int $maxPrecision = null, array $units = []): string
    {
        if (empty($units)) {
            $units = [
                3 => 'K',
                6 => 'M',
                9 => 'B',
                12 => 'T',
                15 => 'Q',
            ];
        }

        switch (true) {
            case (float) $number === 0.0:
                return $precision > 0 ? static::format(0, $precision, $maxPrecision) : '0';
            case $number < 0:
                return sprintf('-%s', static::summarize(abs($number), $precision, $maxPrecision, $units));
            case $number >= 1e15:
                return sprintf('%s'.end($units), static::summarize($number / 1e15, $precision, $maxPrecision, $units));
        }

        $numberExponent = floor(log10($number));
        $displayExponent = $numberExponent - ($numberExponent % 3);
        $number /= pow(10, $displayExponent);

        return trim(sprintf('%s%s', static::format($number, $precision, $maxPrecision), $units[$displayExponent] ?? ''));
    }

    public static function clamp(int|float $number, int|float $min, int|float $max): int|float
    {
        return min(max($number, $min), $max);
    }

    public static function pairs(int|float $to, int|float $by, int|float $start = 0, int|float $offset = 1): array
    {
        $output = [];

        for ($lower = $start; $lower < $to; $lower += $by) {
            $upper = $lower + $by - $offset;

            if ($upper > $to) {
                $upper = $to;
            }

            $output[] = [$lower, $upper];
        }

        return $output;
    }

    public static function trim(int|float $number): int|float
    {
        return json_decode(json_encode($number));
    }

    public static function withLocale(string $locale, callable $callback): mixed
    {
        $previousLocale = static::$locale;
        static::useLocale($locale);

        try {
            return $callback();
        } finally {
            static::useLocale($previousLocale);
        }
    }

    public static function withCurrency(string $currency, callable $callback): mixed
    {
        $previousCurrency = static::$currency;
        static::useCurrency($currency);

        try {
            return $callback();
        } finally {
            static::useCurrency($previousCurrency);
        }
    }

    public static function useLocale(string $locale): void
    {
        static::$locale = $locale;
    }

    public static function useCurrency(string $currency): void
    {
        static::$currency = $currency;
    }

    public static function defaultLocale(): string
    {
        return static::$locale;
    }

    public static function defaultCurrency(): string
    {
        return static::$currency;
    }

    protected static function formatWithNative(int|float $number, int $decimals, string $locale): string
    {
        $separators = self::separatorsForLocale($locale);

        return number_format($number, $decimals, $separators['decimal'], $separators['thousands']);
    }

    /**
     * @return array{decimal: string, thousands: string}
     */
    protected static function separatorsForLocale(string $locale): array
    {
        $locale = strtolower(str_replace('_', '-', $locale));

        if (str_starts_with($locale, 'id')) {
            return ['decimal' => ',', 'thousands' => '.'];
        }

        return ['decimal' => '.', 'thousands' => ','];
    }
}
