<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerizinanTebangPohonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'surat_permohonan' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'ktp_nib' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'alasan_penebangan' => ['required', 'string', 'min:10', 'max:5000'],
            'foto_pohon' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'rencana_ganti_tanam' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'surat_permohonan.max' => 'Surat permohonan maksimal 5MB.',
            'ktp_nib.max' => 'Dokumen KTP/NIB maksimal 5MB.',
            'foto_pohon.max' => 'Ukuran foto maksimal 2MB.',
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'email.required' => 'Email wajib diisi untuk menerima notifikasi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
