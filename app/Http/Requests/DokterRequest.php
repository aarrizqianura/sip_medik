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
        $dokter = $this->route('dokter');
        $userId = $dokter ? $dokter->id_user : null;
        $dokterId = $dokter ? $dokter->id : null;
        $isLocked = $dokter && $dokter->is_str_locked;

        $passwordRule = $this->isMethod('POST') ? 'required|string|min:6' : 'nullable|string|min:6';
        $fileStrRule = $this->isMethod('POST') ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';

        $rules = [
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
            'subspesialis' => 'nullable|string|max:150',
            'tempat_praktik_ke' => 'required|in:1,2,3',
            'status_kepegawaian' => 'required|in:PNS,PPPK,BLUD,Mitra',
            'status_str' => 'required|in:seumur_hidup,berkala',
            'tanggal_terbit_str' => 'required|date',
            'tanggal_kadaluarsa_str' => 'nullable|date|required_if:status_str,berkala',
            'file_str' => $fileStrRule,
        ];

        // Jika data STR terkunci, nomor STR tidak boleh diubah
        if ($isLocked) {
            $rules['nomor_str'] = ['nullable', 'string'];
        } else {
            $rules['nomor_str'] = [
                'required',
                'digits:16',
                Rule::unique('dokters', 'nomor_str')->ignore($dokterId),
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dokter = $this->route('dokter');
            if ($dokter && $dokter->is_str_locked) {
                if ($this->filled('nomor_str') && $this->input('nomor_str') !== $dokter->nomor_str) {
                    $validator->errors()->add('nomor_str', 'Nomor STR terkunci (read-only) karena telah tervalidasi oleh Subkomite Kredensial.');
                }
            }
        });
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
            'spesialisasi.required' => 'Kualifikasi spesialisasi dokter wajib diisi.',
            'tempat_praktik_ke.required' => 'Deklarasi tempat praktik keberapa di RSUD Ansari Saleh wajib dipilih (SIP ke-1, ke-2, atau ke-3).',
            'tempat_praktik_ke.in' => 'Deklarasi tempat praktik harus antara SIP ke-1, ke-2, atau ke-3.',
            'status_kepegawaian.required' => 'Status kepegawaian wajib dipilih.',
            'status_str.required' => 'Status masa berlaku STR wajib dipilih.',
            'nomor_str.required' => 'Nomor registrasi STR 16 digit wajib diisi.',
            'nomor_str.digits' => 'Nomor registrasi STR resmi KKI/Kemenkes harus tepat 16 digit angka.',
            'nomor_str.unique' => 'Nomor registrasi STR ini telah terdaftar untuk dokter lain.',
            'tanggal_terbit_str.required' => 'Tanggal terbit STR wajib diisi.',
            'tanggal_kadaluarsa_str.required_if' => 'Tanggal kadaluarsa STR wajib diisi apabila status STR Berkala.',
            'file_str.required' => 'Salinan digital e-STR resmi dari KKI/Kemenkes wajib diunggah.',
            'file_str.mimes' => 'Format berkas e-STR harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_str.max' => 'Ukuran salinan digital e-STR maksimal 5 MB.',
        ];
    }
}
