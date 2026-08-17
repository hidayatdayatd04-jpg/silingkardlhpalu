<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermohonanRekomendasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'npwp' => ['required', 'string', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}$/'],
            'jenis_usaha' => ['required', 'string', 'max:255'],
            'jenis_usaha_lainnya' => ['required_if:jenis_usaha,Lainnya', 'nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string', 'max:2000'],
            'nomor_telepon' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:255'],
            'surat_permohonan' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'dokumen_pendukung' => ['required', 'array', 'min:1', 'max:10'],
            'dokumen_pendukung.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'npwp.regex' => 'Format NPWP tidak valid. Contoh: 12.345.678.9-012.345',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid.',
            'surat_permohonan.max' => 'Surat permohonan maksimal 5MB.',
            'dokumen_pendukung.required' => 'Minimal unggah 1 dokumen pendukung.',
        ];
    }
}
