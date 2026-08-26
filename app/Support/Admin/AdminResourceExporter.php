<?php

namespace App\Support\Admin;

use App\Models\User;
use App\Support\DataIO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Exporter tunggal untuk seluruh tabel resource admin.
 *
 * Kolom tabel utama tetap lengkap (kecuali data autentikasi/rahasia), lalu
 * relasi lampiran diringkas per record agar CSV dan XLSX selalu memiliki satu
 * baris untuk satu record. Semua URL file melalui proxy admin, bukan URL
 * storage langsung atau signed URL yang dapat bocor/berakhir masa berlakunya.
 */
class AdminResourceExporter
{
    /**
     * @return array{columns: array<string,string>, direct_file_columns: array<int,string>, relation_columns: array<string,array>}
     */
    public function definition(array $meta): array
    {
        $columns = AdminRegistry::exportColumns($meta['slug'], $meta['model']);
        $directFileColumns = [];

        foreach (AdminRegistry::formFields($meta) as $field) {
            $name = $field['name'] ?? null;

            if (($field['type'] ?? null) === 'file' && is_string($name) && array_key_exists($name, $columns)) {
                $directFileColumns[] = $name;
                $columns[$name] = ($field['label'] ?? Str::headline($name)).' (Tautan aman)';
            }
        }

        // Foto profil dan role tidak tersimpan sebagai kolom biasa yang aman
        // untuk diekspor, tetapi keduanya penting untuk administrasi pengguna.
        if ($meta['slug'] === 'user') {
            $columns['__photo_profile'] = 'Foto Profil (Tautan aman)';
            $columns['__roles'] = 'Role Pengguna';
        }

        $relationColumns = [];
        foreach (AdminRegistry::relationUploads($meta['slug']) as $upload) {
            $relation = $upload['relation'] ?? null;
            if (! is_string($relation) || $relation === '') {
                continue;
            }

            $key = '__relation_'.$relation;
            $columns[$key] = ($upload['label'] ?? Str::headline($relation)).' (Data & Tautan aman)';
            $relationColumns[$key] = $upload;
        }

        if ($meta['slug'] === 'pelanggaran') {
            $columns['__sanksi'] = 'Sanksi (Data & Tautan aman)';
            $columns['__sidak_objek'] = 'Sidak / Objek Pengawasan';
        }

        if ($meta['slug'] === 'sosialisasi') {
            $columns['__pesertas'] = 'Peserta / Sertifikat (Data & Tautan aman)';
        }

        return [
            'columns' => $columns,
            'direct_file_columns' => array_values(array_unique($directFileColumns)),
            'relation_columns' => $relationColumns,
        ];
    }

    public function prepareQuery(Builder $query, array $meta): Builder
    {
        $relations = collect(AdminRegistry::relationUploads($meta['slug']))
            ->pluck('relation')
            ->filter(fn ($relation) => is_string($relation) && $relation !== '')
            ->values()
            ->all();

        if ($meta['slug'] === 'user') {
            $relations[] = 'roles';
        }

        if ($meta['slug'] === 'pelanggaran') {
            $relations[] = 'sanksi';
            $relations[] = 'sidak.objekPengawasan';
        }

        if ($meta['slug'] === 'sosialisasi') {
            $relations[] = 'pesertas.objekPengawasan';
        }

        return $query->with(array_values(array_unique($relations)));
    }

    /**
     * @return array<int,mixed>
     */
    public function row(Model $record, array $meta, array $definition): array
    {
        $values = [];

        foreach ($definition['columns'] as $column => $heading) {
            if ($column === '__photo_profile') {
                $values[] = $this->fileUrl((string) ($record->photo_path ?? ''), $meta['slug']);

                continue;
            }

            if ($column === '__roles') {
                $values[] = $record instanceof User
                    ? $record->roles
                        ->map(fn ($role) => \App\Support\AdminAccess::roleLabel($role->name))
                        ->filter()
                        ->implode(', ')
                    : '-';

                continue;
            }

            if ($column === '__sanksi') {
                $values[] = $this->formatRelated($record->sanksi, $meta['slug'], ['surat_path']);

                continue;
            }

            if ($column === '__sidak_objek') {
                $sidak = $record->sidak;
                $parts = [];
                if ($sidak) {
                    $parts[] = 'Sidak: '.$this->formatAttributes($sidak, $meta['slug']);
                    if ($sidak->objekPengawasan) {
                        $parts[] = 'Objek Pengawasan: '.$this->formatAttributes($sidak->objekPengawasan, $meta['slug']);
                    }
                }
                $values[] = $parts === [] ? '-' : implode("\n", $parts);

                continue;
            }

            if ($column === '__pesertas') {
                $values[] = $this->formatRelated($record->pesertas, $meta['slug'], ['sertifikat_path']);

                continue;
            }

            if (isset($definition['relation_columns'][$column])) {
                $upload = $definition['relation_columns'][$column];
                $values[] = $this->formatRelated(
                    $record->{$upload['relation']},
                    $meta['slug'],
                    [(string) ($upload['path_field'] ?? 'path')],
                );

                continue;
            }

            $value = $record->getAttribute($column);
            $values[] = in_array($column, $definition['direct_file_columns'], true)
                ? $this->fileUrl(is_string($value) ? $value : '', $meta['slug'])
                : $value;
        }

        return $values;
    }

    public function csvDownload(Builder $query, array $meta, string $filename)
    {
        $definition = $this->definition($meta);
        $query = $this->prepareQuery($query, $meta);

        return DataIO::csvDownload(
            $query,
            array_keys($definition['columns']),
            $filename,
            array_values($definition['columns']),
            fn (Model $record) => $this->row($record, $meta, $definition),
        );
    }

    public function write(Builder $query, array $meta, string $format, string $absolutePath): void
    {
        $definition = $this->definition($meta);
        $query = $this->prepareQuery($query, $meta);
        $columns = array_keys($definition['columns']);
        $headings = array_values($definition['columns']);
        $mapper = fn (Model $record) => $this->row($record, $meta, $definition);

        if ($format === 'csv') {
            DataIO::writeCsvFile($query, $columns, $absolutePath, $headings, $mapper);

            return;
        }

        DataIO::writeXlsx($query, $columns, $absolutePath, $headings, $mapper);
    }

    protected function fileUrl(string $path, string $slug): string
    {
        return AdminRegistry::isAllowedFilePath($path, $slug)
            ? AdminRegistry::previewUrl($path, $slug)
            : '-';
    }

    /**
     * @param  iterable<Model>|Model|null  $related
     * @param  array<int,string>  $fileFields
     */
    protected function formatRelated(iterable|Model|null $related, string $slug, array $fileFields = []): string
    {
        if ($related instanceof Model) {
            return $this->formatAttributes($related, $slug, $fileFields);
        }

        if (! $related instanceof \Traversable && ! is_array($related)) {
            return '-';
        }

        $items = collect($related)
            ->filter(fn ($item) => $item instanceof Model)
            ->map(fn (Model $item) => $this->formatAttributes($item, $slug, $fileFields))
            ->filter(fn (string $value) => $value !== '-');

        return $items->isEmpty() ? '-' : $items->implode("\n\n");
    }

    /**
     * Ringkas semua atribut yang aman dari satu record relasi dalam satu sel.
     * Tidak membocorkan password/token meskipun suatu model kelak memiliki
     * atribut tersebut.
     *
     * @param  array<int,string>  $fileFields
     */
    protected function formatAttributes(Model $record, string $slug, array $fileFields = []): string
    {
        $secretFields = ['password', 'remember_token', 'token', 'two_factor_secret', 'two_factor_recovery_codes'];

        $parts = [];
        foreach ($record->getAttributes() as $key => $rawValue) {
            if (in_array($key, $secretFields, true)) {
                continue;
            }

            $value = in_array($key, $fileFields, true)
                ? $this->fileUrl((string) $rawValue, $slug)
                : DataIO::displayValue($record->getAttribute($key));

            $parts[] = AdminRegistry::labelForField($key).': '.$value;
        }

        return $parts === [] ? '-' : implode('; ', $parts);
    }
}
