<?php

namespace App\Http\Requests;

use App\Enums\JenisPengaduanTataPenataan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengaduanTataPenataanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'jenis_pengaduan' => ['required', 'string', Rule::in(array_column(JenisPengaduanTataPenataan::cases(), 'value'))],
            'nama_terlapor' => ['nullable', 'string', 'max:255'],
            'nama_perusahaan_terlapor' => ['nullable', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'email.required' => 'Email wajib diisi untuk menerima notifikasi.',
            'email.email' => 'Format email tidak valid.',
            'photos.required' => 'Minimal unggah 1 foto bukti.',
            'photos.*.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
