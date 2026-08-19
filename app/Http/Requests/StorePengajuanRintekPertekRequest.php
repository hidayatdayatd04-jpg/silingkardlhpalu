<?php

namespace App\Http\Requests;

use App\Models\PengajuanRintekPertek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengajuanRintekPertekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $imagePdfRule = ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'];

        return [
            'registrasi_usaha_lb3_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '' && $value !== '__lainnya__') {
                        if (! is_numeric($value) || ! \App\Models\RegistrasiUsahaLb3::where('id', $value)->exists()) {
                            $fail('Perusahaan terdaftar LB3 tidak valid.');
                        }
                    }
                },
            ],
            'nama_perusahaan' => [
                'nullable',
                'required_unless:registrasi_usaha_lb3_id,__lainnya__',
                'string',
                'max:255',
            ],
            'nama_perusahaan_lainnya' => [
                'nullable',
                'required_if:registrasi_usaha_lb3_id,__lainnya__',
                'string',
                'max:255',
            ],
            'nama_penanggung_jawab' => ['required', 'string', 'max:255'],
            'nomor_nib' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'jenis_usaha' => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string', 'max:2000'],
            'nomor_telepon' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'jenis_pengajuan' => ['required', 'string', Rule::in(array_keys(PengajuanRintekPertek::JENIS_PENGAJUAN_OPTIONS))],
            'keterangan_tambahan' => ['nullable', 'string', 'max:5000'],
            'surat_permohonan' => $imagePdfRule,
            'dplh_ukl_upl' => $imagePdfRule,
            'nib' => $imagePdfRule,
            'sppl' => $imagePdfRule,
            'denah_tps_lb3' => $imagePdfRule,
            'sop_tanggap_darurat' => $imagePdfRule,
        ];
    }

    public function messages(): array
    {
        return [
            'nama_perusahaan.required_unless' => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan_lainnya.required_if' => 'Nama perusahaan wajib diisi.',
            '*.max' => 'Ukuran dokumen maksimal 5MB.',
            '*.mimes' => 'Format dokumen tidak valid. Hanya menerima PDF, Word (DOC/DOCX), Excel (XLS/XLSX), JPG, JPEG, PNG, WEBP, AVIF, HEIC, dan HEIF.',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid. Gunakan nomor Indonesia yang aktif.',
            'jenis_pengajuan.in' => 'Jenis pengajuan tidak valid.',
        ];
    }
}
