<?php

namespace Tests\Unit;

use App\Support\DataIO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataIOTest extends TestCase
{
    #[DataProvider('formulaInjectionProvider')]
    public function test_sanitize_cell_menetralkan_formula_injection(string $input, string $expected): void
    {
        $this->assertSame($expected, DataIO::sanitizeCell($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function formulaInjectionProvider(): array
    {
        return [
            'formula =CMD' => ['=CMD("calc")', "'=CMD(\"calc\")"],
            'penjumlahan +' => ['+1+2', "'+1+2"],
            'formula -' => ['-2+3', "'-2+3"],
            'formula @SUM' => ['@SUM(A1)', "'@SUM(A1)"],
            'tab di awal' => ["\tfoo", "'\tfoo"],
            'carriage return di awal' => ["\rfoo", "'\rfoo"],
            'bilangan bulat' => ['123', '123'],
            'bilangan negatif' => ['-45.6', '-45.6'],
            'bilangan desimal' => ['3.14', '3.14'],
            'teks biasa' => ['Nama Taman', 'Nama Taman'],
            'string kosong' => ['', ''],
        ];
    }
}
