<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DokterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->route('dokter') ? $this->route('dokter')->id_user : null;
        $dokterId = $this->route('dokter') ? $this->route('dokter')->id : null;

        $passwordRule = $this->isMethod('POST') ? 'required|string|min:6' : 'nullable|string|min:6';

        return [
            'nama' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'no_wa' => 'required|string|max:20',
            'password' => $passwordRule,
            'nip_nrp' => [
                'required',
                'string',
                'max:30',
                Rule::unique('dokters', 'nip_nrp')->ignore($dokterId),
            ],
            'id_ksm' => 'required|exists:ksm,id',
            'spesialisasi' => 'required|string|max:100',
            'status_kepegawaian' => 'required|in:PNS,PPPK,BLUD,Mitra',
            'status_str' => 'required|in:seumur_hidup,berkala',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap dokter wajib diisi.',
            'username.required' => 'Username login dokter wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi untuk pengiriman notifikasi pengingat.',
            'nip_nrp.required' => 'NIP atau NRP dokter wajib diisi.',
            'nip_nrp.unique' => 'NIP/NRP ini sudah terdaftar.',
            'id_ksm.required' => 'KSM (Kelompok Staf Medis) wajib dipilih.',
            'spesialisasi.required' => 'Spesialisasi dokter wajib diisi.',
            'status_kepegawaian.required' => 'Status kepegawaian wajib dipilih.',
            'status_str.required' => 'Status masa berlaku STR wajib dipilih.',
        ];
    }
}
