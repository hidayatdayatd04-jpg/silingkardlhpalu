<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrasiUsahaLb3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'alamat' => ['required', 'string', 'max:1000'],
            'jenis_lb3_id' => ['required', 'integer', Rule::exists('jenis_lb3s', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid. Gunakan nomor Indonesia yang aktif.',
            'email.required' => 'Email wajib diisi untuk menerima notifikasi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
