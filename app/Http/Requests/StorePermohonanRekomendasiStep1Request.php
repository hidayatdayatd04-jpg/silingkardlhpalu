<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class StorePermohonanRekomendasiStep1Request extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'npwp.regex' => 'Format NPWP tidak valid. Contoh: 12.345.678.9-012.345',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid.',
        ];
    }
}
