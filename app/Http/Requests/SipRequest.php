<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $fileRules = $this->isMethod('POST') ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';

        return [
            'id_dokter' => 'nullable|exists:dokters,id',
            'nomor_sip' => 'required|string|max:100',
            'tempat_praktik_ke' => 'required|integer|in:1,2,3',
            'tanggal_terbit' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after:tanggal_terbit',
            'file_sip' => $fileRules,
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_sip.required' => 'Nomor SIP wajib diisi.',
            'tempat_praktik_ke.required' => 'Tempat praktik ke (1, 2, atau 3) wajib ditentukan.',
            'tempat_praktik_ke.in' => 'SIP tempat praktik hanya boleh 1, 2, atau 3.',
            'tanggal_terbit.required' => 'Tanggal terbit SIP wajib diisi.',
            'tanggal_kadaluarsa.required' => 'Tanggal kedaluwarsa SIP wajib diisi.',
            'tanggal_kadaluarsa.after' => 'Tanggal kedaluwarsa harus setelah tanggal terbit.',
            'file_sip.required' => 'Berkas pindaian (scan) SIP wajib diunggah.',
            'file_sip.mimes' => 'Format berkas hanya diperbolehkan PDF, JPG, atau PNG.',
            'file_sip.max' => 'Ukuran berkas maksimal 2 MB.',
        ];
    }
}
