<?php

namespace App\Http\Requests;

use App\Enums\JenisPengaduanSampah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengaduanSampahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'jenis_pengaduan' => ['required', Rule::enum(JenisPengaduanSampah::class)],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'alamat' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['required', 'array', 'min:1', 'max:3'],
            'photos.*' => ['mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'photos.required' => 'Minimal 1 foto bukti wajib diunggah.',
            'photos.*.max' => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
