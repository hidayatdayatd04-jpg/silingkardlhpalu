<?php

namespace App\Imports;

use App\Support\DataIO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Import generik untuk semua resource admin — BERBASIS DataIO (dependency-free).
 *
 * Header file (A1) di-ke-bawah ke snake_case, dicocokkan ke kolom fillable.
 * Enum label dibalik ke value mentah; kolom readonly diabaikan.
 */
class ResourceImport
{
    public int $imported = 0;

    /** @var array<int,string> */
    public array $errors = [];

    /**
     * @param  array  $meta  AdminRegistry resource meta
     */
    public function __construct(protected array $meta)
    {
    }

    /**
     * Impor file (path absolut) — return jumlah berhasil.
     */
    public function importFromFile(string $absolutePath): int
    {
        $rows = DataIO::readFile($absolutePath);
        $this->process($rows);

        return $this->imported;
    }

    /**
     * Proses koleksi baris (key: heading snake/lower). Kompatibilitas untuk test.
     *
     * @param  \Illuminate\Support\Collection|array  $rows
     */
    public function collection($rows): void
    {
        $this->process($rows);
    }

    /**
     * @param  iterable  $rows
     */
    protected function process($rows): void
    {
        $model = new $this->meta['model'];
        $fillable = $model->getFillable();
        $readonly = ['id', 'nomor_tiket', 'nomor_pengajuan', 'nomor_registrasi', 'nomor_sidak', 'nomor_pelanggaran', 'nomor_sanksi', 'created_at', 'updated_at', 'password', 'remember_token', 'email_verified_at', 'additional_access', 'photo_path', 'preferences'];
        $enumMaps = $this->enumMaps($model);

        $rowIndex = 0;
        foreach ($rows as $row) {
            $rowIndex++;
            // Baris bisa berupa array asosiatif atau Collection.
            $assoc = is_array($row) ? $row : (method_exists($row, 'all') ? $row->all() : (array) $row);
            $data = [];

            foreach ($assoc as $heading => $value) {
                $column = $this->matchColumn((string) $heading, $fillable);
                if (! $column || in_array($column, $readonly, true)) {
                    continue;
                }
                if ($value === null || $value === '' || $value === '-') {
                    continue;
                }
                if (isset($enumMaps[$column]) && isset($enumMaps[$column][$value])) {
                    $value = $enumMaps[$column][$value];
                }
                $data[$column] = $value;
            }

            if (empty($data)) {
                continue;
            }

            try {
                $model->newInstance()->fill($data)->save();
                $this->imported++;
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$rowIndex}: ".$e->getMessage();
            }
        }
    }

    /**
     * Cocokkan heading (lower/snake) ke kolom fillable. Toleran terhadap "Headline".
     */
    protected function matchColumn(string $heading, array $fillable): ?string
    {
        $normalized = Str::of($heading)->lower()->replace(' ', '_')->toString();
        if (in_array($normalized, $fillable, true)) {
            return $normalized;
        }
        foreach ($fillable as $column) {
            if (strtolower(Str::headline($column)) === strtolower($heading) || Str::slug(Str::headline($column), '_') === $normalized) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return array<string, array<string,string>>
     */
    protected function enumMaps(Model $model): array
    {
        $maps = [];
        foreach ($model->getCasts() as $column => $cast) {
            if (is_string($cast) && enum_exists($cast) && method_exists($cast, 'cases')) {
                foreach ($cast::cases() as $case) {
                    $label = method_exists($case, 'label') ? $case->label() : $case->value;
                    $maps[$column][$label] = $case->value;
                    $maps[$column][$case->value] = $case->value;
                }
            }
        }

        return $maps;
    }
}
