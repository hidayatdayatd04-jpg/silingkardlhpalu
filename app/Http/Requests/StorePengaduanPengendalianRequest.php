<?php

namespace App\Http\Requests;

use App\Enums\JenisPengaduanPengendalian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengaduanPengendalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'jenis_pengaduan' => ['required', 'string', Rule::in(array_column(JenisPengaduanPengendalian::cases(), 'value'))],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'photos.required' => 'Minimal unggah 1 foto bukti.',
            'photos.*.max' => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
