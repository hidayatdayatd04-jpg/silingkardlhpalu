<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\TamanKota;

class StorePermohonanPinjamTamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $officialTamanIds = TamanKota::whereIn('nama', [
            'Taman Vatulemo',
            'Taman Gor',
            'Taman Nasional',
            'Taman Doyata',
            'Taman Lasoso',
        ])->pluck('id')->map(fn ($id) => (string) $id)->all();

        return [
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'taman_kota_id' => ['nullable', Rule::in(array_merge($officialTamanIds, ['__lainnya__']))],
            'nama_taman_manual' => ['required_if:taman_kota_id,__lainnya__', 'nullable', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date', 'after:now'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_kegiatan'],
            'surat_permohonan' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'jaminan_kebersihan' => ['accepted'],
            'surat_jaminan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'jaminan_kebersihan.accepted' => 'Anda harus menyetujui janji menjaga kebersihan taman.',
            'surat_permohonan.max' => 'Surat permohonan maksimal 5MB.',
            'surat_jaminan.max' => 'Surat jaminan maksimal 5MB.',
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'email.required' => 'Email wajib diisi untuk menerima notifikasi.',
            'email.email' => 'Format email tidak valid.',
            'taman_kota_id.in' => 'Taman yang dipilih tidak valid.',
            'nama_taman_manual.required_if' => 'Nama taman wajib diisi saat memilih opsi Lainnya.',
        ];
    }
}
