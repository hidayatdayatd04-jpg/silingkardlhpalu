<?php

namespace App\Support;

class PhoneNormalizer
{
    /**
     * Normalize an Indonesian phone number to international format (628xx).
     */
    public static function normalize(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '628')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '08')) {
            return '62' . substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '8') && strlen($cleaned) >= 9) {
            return '62' . $cleaned;
        }

        if (str_starts_with($cleaned, '62') && strlen($cleaned) >= 10) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Validate that a phone number matches Indonesian format.
     */
    public static function isValidIndonesianPhone(string $phone): bool
    {
        return (bool) preg_match('/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/', $phone);
    }
}
