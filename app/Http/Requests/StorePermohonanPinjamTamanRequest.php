<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermohonanPinjamTamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $officialTamans = [
            'Taman Vatulemo',
            'Taman Gor',
            'Taman Bundaran Nasional',
            'Taman Nasional',
            'Taman Doyata',
            'Taman Lasoso',
        ];

        return [
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'nama_taman' => ['nullable', Rule::in($officialTamans)],
            'tanggal_kegiatan' => ['required', 'date', 'after:now'],
            'tanggal_selesai' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $start = request()->input('tanggal_kegiatan') ?? $this->tanggal_kegiatan;
                    if ($start && $value) {
                        try {
                            $startTime = \Illuminate\Support\Carbon::parse($start);
                            $endTime = \Illuminate\Support\Carbon::parse($value);
                            if ($endTime->lessThan($startTime->copy()->addHour())) {
                                $fail(__('Tanggal & jam selesai tidak boleh sebelum tanggal mulai dan harus minimal 1 jam sesudah tanggal & jam mulai.'));
                            }
                        } catch (\Throwable $e) {
                            // Handled by date rule
                        }
                    }
                },
            ],
            'surat_permohonan' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'jaminan_kebersihan' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'jaminan_kebersihan.accepted' => 'Anda harus menyetujui janji menjaga kebersihan taman.',
            'surat_permohonan.max' => 'Surat permohonan maksimal 5MB.',
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia yang aktif.',
            'nama_taman.in' => 'Taman yang dipilih tidak valid.',
        ];
    }
}
