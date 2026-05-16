<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkingCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_campaign' => ['required', 'string', 'max:255'],
            'target_method' => ['required', 'string', Rule::in([Campaign::INPUT_MANUAL, Campaign::INPUT_EXCEL])],
            'manual_numbers' => ['nullable', 'string', 'required_if:target_method,' . Campaign::INPUT_MANUAL],
            'excel_file' => ['nullable', 'file', 'required_if:target_method,' . Campaign::INPUT_EXCEL, 'mimes:csv,xlsx,txt', 'max:5120'],
            'bulking_pesan' => ['required', 'string'],
            'bulking_media' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,pdf,docx,zip'],
            'min_delay' => ['required', 'integer', 'min:0'],
            'max_delay' => ['required', 'integer', 'min:0'],
            'batch_size' => ['required', 'integer', 'min:1'],
            'rest_after_batch' => ['required', 'integer', 'min:0'],
            'retry_limit' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_campaign.required' => 'Nama campaign wajib diisi.',
            'target_method.required' => 'Metode input target wajib dipilih.',
            'target_method.in' => 'Metode input target tidak valid.',
            'manual_numbers.required_if' => 'Daftar nomor manual wajib diisi saat mode manual dipilih.',
            'excel_file.required_if' => 'File Excel/CSV wajib dipilih saat mode import file dipilih.',
            'excel_file.mimes' => 'File target harus berformat .xlsx atau .csv.',
            'excel_file.max' => 'Ukuran file target maksimal 5MB.',
            'bulking_pesan.required' => 'Isi pesan bulking wajib diisi.',
            'bulking_media.max' => 'Ukuran file media maksimal 4MB.',
            'bulking_media.mimes' => 'Format file media tidak didukung atau berbahaya.',
            'min_delay.required' => 'Minimal delay wajib diisi.',
            'max_delay.required' => 'Maksimal delay wajib diisi.',
            'batch_size.required' => 'Batch size wajib diisi.',
            'rest_after_batch.required' => 'Cooldown antar batch wajib diisi.',
            'retry_limit.required' => 'Retry limit wajib diisi.',
        ];
    }
}
