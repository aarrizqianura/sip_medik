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
        $fileRules = $this->isMethod('POST') ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048';

        return [
            'id_dokter' => 'nullable|exists:dokters,id',
            'nomor_sip' => 'required|string|max:100',
            'tempat_praktik_ke' => 'required|integer|in:1,2,3',
            'fasyankes' => 'nullable|string|in:RSUD dr. H. Moch. Ansari Saleh,RSUD Dr. H. Moch. Ansari Saleh',
            'alamat_fasyankes' => 'nullable|string|max:255',
            'penerbit_sip' => 'nullable|string|max:150',
            'status_kecukupan_skp' => 'nullable|boolean',
            'nomor_rekomendasi_skp' => 'nullable|string|max:150',
            'file_rekomendasi_skp' => 'nullable|file|mimes:pdf|max:2048',
            'tanggal_terbit' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after:tanggal_terbit',
            'file_sip' => $fileRules,
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Syarat perpanjangan izin: Harus menandai status kecukupan SKP ATAU mengunggah Surat Rekomendasi SKP SatuSehat
            $statusSkp = filter_var($this->input('status_kecukupan_skp'), FILTER_VALIDATE_BOOLEAN);
            $hasFileSkp = $this->hasFile('file_rekomendasi_skp');

            if (! $statusSkp && ! $hasFileSkp) {
                $validator->errors()->add(
                    'status_kecukupan_skp',
                    'Prasyarat SIP: Anda wajib menandai status kecukupan Satuan Kredit Profesi (SKP) atau melampirkan Surat Rekomendasi Kecukupan SKP dari platform SatuSehat Kemenkes sebagai syarat perpanjangan izin.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'nomor_sip.required' => 'Nomor izin SIP dari DPMPTSP wajib diisi.',
            'tempat_praktik_ke.required' => 'SIP tempat praktik ke (1, 2, atau 3) wajib ditentukan.',
            'tempat_praktik_ke.in' => 'SIP tempat praktik hanya boleh 1, 2, atau 3.',
            'fasyankes.in' => 'Alamat fasyankes perizinan harus tertuju khusus ke RSUD dr. H. Moch. Ansari Saleh.',
            'tanggal_terbit.required' => 'Tanggal terbit SIP wajib diisi.',
            'tanggal_kadaluarsa.required' => 'Tanggal jatuh tempo kedaluwarsa SIP (masa 5 tahun) wajib diisi.',
            'tanggal_kadaluarsa.after' => 'Tanggal jatuh tempo harus setelah tanggal terbit.',
            'file_sip.required' => 'Berkas pindaian (scan) lembar SIP asli wajib diunggah.',
            'file_sip.mimes' => 'Format berkas pindaian SIP asli wajib berupa format PDF.',
            'file_sip.max' => 'Ukuran berkas pindaian lembar SIP maksimal 2 MB.',
            'file_rekomendasi_skp.mimes' => 'Format berkas Surat Rekomendasi Kecukupan SKP SatuSehat harus PDF.',
            'file_rekomendasi_skp.max' => 'Ukuran berkas Surat Rekomendasi SKP maksimal 2 MB.',
        ];
    }
}
