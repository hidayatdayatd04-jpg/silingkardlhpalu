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
 * relasi lampiran dan berkas diformat rapi per record:
 * 1. Nama File - (Link Url)
 * 2. Nama File - (Link Url)
 *
 * Semua URL file melalui proxy web admin, bukan URL storage langsung atau
 * signed URL yang dapat bocor/berakhir masa berlakunya.
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
                $columns[$name] = $field['label'] ?? Str::headline($name);
            }
        }

        // Foto profil dan role tidak tersimpan sebagai kolom biasa yang aman
        // untuk diekspor, tetapi keduanya penting untuk administrasi pengguna.
        if ($meta['slug'] === 'user') {
            $columns['__photo_profile'] = 'Foto Profil';
            $columns['__roles'] = 'Role Pengguna';
        }

        $relationColumns = [];
        foreach (AdminRegistry::relationUploads($meta['slug']) as $upload) {
            $relation = $upload['relation'] ?? null;
            if (! is_string($relation) || $relation === '') {
                continue;
            }

            $key = '__relation_'.$relation;
            $columns[$key] = $upload['label'] ?? Str::headline($relation);
            $relationColumns[$key] = $upload;
        }

        if ($meta['slug'] === 'pelanggaran') {
            $columns['__sanksi'] = 'Sanksi';
            $columns['__sidak_objek'] = 'Sidak / Objek Pengawasan';
        }

        if ($meta['slug'] === 'sosialisasi') {
            $columns['__pesertas'] = 'Daftar Peserta / Sertifikat';
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
                $values[] = $this->formatSingleFile((string) ($record->photo_path ?? ''), 'user');

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
                $sanksi = $record->sanksi;
                if (! $sanksi) {
                    $values[] = '-';

                    continue;
                }

                $parts = [];
                if ($sanksi->jenis_sanksi) {
                    $parts[] = 'Jenis: '.$sanksi->jenis_sanksi->label();
                }
                if ($sanksi->status_sanksi) {
                    $parts[] = 'Status: '.$sanksi->status_sanksi->label();
                }
                if ($sanksi->batas_waktu_perbaikan) {
                    $parts[] = 'Batas Waktu: '.$sanksi->batas_waktu_perbaikan->format('d M Y');
                }
                if (filled($sanksi->surat_path)) {
                    $fileFormatted = $this->formatSingleFile($sanksi->surat_path, 'sanksi');
                    if ($fileFormatted !== '-') {
                        $parts[] = 'Surat Sanksi: '.$fileFormatted;
                    }
                }

                $values[] = $parts === [] ? '-' : implode("\n", $parts);

                continue;
            }

            if ($column === '__sidak_objek') {
                if ($record->sidak) {
                    $sidak = $record->sidak;
                    $namaPerusahaan = $sidak->objekPengawasan?->nama_perusahaan;
                    $tgl = $sidak->tanggal_sidak?->format('d M Y');
                    $hasil = $sidak->hasil_label;
                    $desc = ($namaPerusahaan ? $namaPerusahaan.' — ' : '').($tgl ?? '').($hasil ? ' ('.$hasil.')' : '');
                    $values[] = $desc ?: '-';
                } elseif (filled($record->sidak_manual)) {
                    $values[] = $record->sidak_manual;
                } else {
                    $values[] = '-';
                }

                continue;
            }

            if ($column === '__pesertas') {
                $pesertas = $record->pesertas;
                if (! $pesertas || $pesertas->isEmpty()) {
                    $values[] = '-';

                    continue;
                }

                $lines = [];
                $index = 1;
                foreach ($pesertas as $peserta) {
                    $nama = $peserta->nama_perusahaan ?: $peserta->objekPengawasan?->nama_perusahaan ?: 'Peserta '.$index;
                    if (filled($peserta->sertifikat_path)) {
                        $url = $this->fileUrl((string) $peserta->sertifikat_path, 'sosialisasi');
                        $certName = urldecode(basename((string) $peserta->sertifikat_path));
                        $lines[] = $url !== '-' ? "{$index}. {$nama} ({$certName}) - {$url}" : "{$index}. {$nama}";
                    } else {
                        $lines[] = "{$index}. {$nama}";
                    }
                    $index++;
                }

                $values[] = $lines === [] ? '-' : implode("\n", $lines);

                continue;
            }

            if (isset($definition['relation_columns'][$column])) {
                $upload = $definition['relation_columns'][$column];
                $values[] = $this->formatRelationFiles(
                    $record->{$upload['relation']},
                    $meta['slug'],
                    $upload,
                );

                continue;
            }

            $value = $record->getAttribute($column);
            if ($column === 'vegetasi' && is_array($value)) {
                $lines = [];
                $idx = 1;
                foreach ($value as $item) {
                    if (is_array($item) && filled($item['jenis_pohon'] ?? null)) {
                        $lines[] = "{$idx}. " . ($item['jenis_pohon'] ?? '') . ' (' . ($item['jumlah'] ?? '') . ')';
                        $idx++;
                    }
                }
                $values[] = $lines === [] ? '-' : implode("\n", $lines);
            } elseif ($column === 'kapasitas_blok' && is_array($value)) {
                $lines = [];
                $idx = 1;
                foreach ($value as $item) {
                        $detailParts = [];
                        if (filled($item['jumlah_blok'] ?? null)) {
                            $detailParts[] = 'Blok: ' . $item['jumlah_blok'];
                        }
                        if (filled($item['kapasitas_per_blok'] ?? null)) {
                            $detailParts[] = 'Kap/Blok: ' . $item['kapasitas_per_blok'];
                        }
                        if (filled($item['jumlah_makam'] ?? null)) {
                            $detailParts[] = 'Total: ' . $item['jumlah_makam'];
                        }
                        if (filled($item['makam_terisi'] ?? null)) {
                            $detailParts[] = 'Terisi: ' . $item['makam_terisi'];
                        }
                        if (filled($item['makam_kosong'] ?? null)) {
                            $detailParts[] = 'Kosong: ' . $item['makam_kosong'];
                        }
                        $lines[] = "{$idx}. " . ($item['agama'] ?? '') . ' (' . implode(', ', $detailParts) . ')';
                        $idx++;
                    }
                }
                $values[] = $lines === [] ? '-' : implode("\n", $lines);
            } elseif ($column === 'foto_dokumentasi' && is_array($value)) {
                $lines = [];
                $idx = 1;
                foreach ($value as $item) {
                    if (is_string($item) && filled($item)) {
                        $url = $this->formatSingleFile($item, $meta['slug']);
                        $lines[] = "{$idx}. {$url}";
                        $idx++;
                    }
                }
                $values[] = $lines === [] ? '-' : implode("\n", $lines);
            } elseif (in_array($column, $definition['direct_file_columns'], true) || in_array($column, ['foto_dokumentasi_1', 'foto_dokumentasi_2', 'foto_dokumentasi_3'], true)) {
                $values[] = $this->formatSingleFile(is_string($value) ? $value : '', $meta['slug']);
            } else {
                $values[] = $value;
            }
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
        $sheetName = $meta['label'] ?? 'Data';

        if ($format === 'csv') {
            DataIO::writeCsvFile($query, $columns, $absolutePath, $headings, $mapper);

            return;
        }

        DataIO::writeXlsx($query, $columns, $absolutePath, $headings, $mapper, $sheetName);
    }

    protected function fileUrl(string $path, string $slug): string
    {
        return AdminRegistry::isAllowedFilePath($path, $slug)
            ? AdminRegistry::previewUrl($path, $slug)
            : '-';
    }

    /**
     * Format berkas tunggal menjadi link rapi: 1. Nama File - Link Url
     */
    protected function formatSingleFile(string $path, string $slug, ?string $customName = null): string
    {
        if ($path === '') {
            return '-';
        }

        $url = $this->fileUrl($path, $slug);
        if ($url === '-') {
            return '-';
        }

        $fileName = $customName ?: urldecode(basename($path));

        return "1. {$fileName} - {$url}";
    }

    /**
     * Format relasi berkas/foto menjadi daftar bernomor rapi:
     * 1. Nama File - Link Url
     * 2. Nama File - Link Url
     * 3. Nama File - Link Url
     */
    protected function formatRelationFiles(mixed $items, string $slug, array $upload): string
    {
        if (! $items instanceof \Traversable && ! is_array($items)) {
            return '-';
        }

        $lines = [];
        $index = 1;
        $pathField = $upload['path_field'] ?? 'path';
        $nameField = $upload['name_field'] ?? null;

        foreach ($items as $item) {
            if (! $item instanceof Model) {
                continue;
            }

            $path = (string) ($item->getAttribute($pathField) ?? '');
            if ($path === '') {
                continue;
            }

            $url = $this->fileUrl($path, $slug);
            if ($url === '-') {
                continue;
            }

            $name = null;
            if ($nameField && filled($item->getAttribute($nameField))) {
                $name = (string) $item->getAttribute($nameField);
            } elseif (filled($item->getAttribute('nama'))) {
                $name = (string) $item->getAttribute('nama');
            } elseif (filled($item->getAttribute('nama_dokumen'))) {
                $name = (string) $item->getAttribute('nama_dokumen');
            } elseif (filled($item->getAttribute('keterangan'))) {
                $name = (string) $item->getAttribute('keterangan');
            } else {
                $name = urldecode(basename($path));
            }

            $lines[] = "{$index}. {$name} - {$url}";
            $index++;
        }

        return $lines === [] ? '-' : implode("\n", $lines);
    }
}
