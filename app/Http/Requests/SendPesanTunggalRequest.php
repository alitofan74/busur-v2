<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPesanTunggalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nomor' => 'required|numeric|min:10',
            'pesan' => 'required|string',
            'media' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,pdf,docx,zip',
        ];
    }

    public function messages()
    {
        return [
            'nomor.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor.numeric' => 'Nomor harus berupa angka.',
            'nomor.min' => 'Nomor telepon minimal 10 digit.',
            'pesan.required' => 'Pesan tidak boleh kosong.',
            'media.max' => 'Ukuran file maksimal adalah 2MB.',
            'media.mimes' => 'Format file tidak didukung atau berbahaya.',
        ];
    }
}
