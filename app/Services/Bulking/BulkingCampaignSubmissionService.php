<?php

namespace App\Services\Bulking;

use App\Models\Campaign;
use App\Services\Bulking\Exceptions\BulkingTargetParserException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BulkingCampaignSubmissionService
{
    public function __construct(
        protected BulkingTargetParser $targetParser,
        protected BulkingCampaignExecutor $campaignExecutor,
    ) {
    }

    public function submit(array $payload, ?UploadedFile $targetFile = null, ?UploadedFile $mediaFile = null): array
    {
        $mediaPath = null;
        $campaignCreated = false;
        $campaign = null;

        try {
            $inputType = $payload['target_method'];
            $mediaPath = $mediaFile?->store('temp_whatsapp/bulking');
            $settings = [
                'min_delay' => (int) $payload['min_delay'],
                'max_delay' => (int) $payload['max_delay'],
                'batch_size' => (int) $payload['batch_size'],
                'rest_after_batch' => (int) $payload['rest_after_batch'],
                'retry_limit' => (int) $payload['retry_limit'],
            ];

            $parserResult = $inputType === Campaign::INPUT_EXCEL
                ? $this->parseSpreadsheetFile($targetFile)
                : $this->targetParser->parseManual((string) ($payload['manual_numbers'] ?? ''));

            if (($parserResult['targets'] ?? []) === []) {
                throw new BulkingTargetParserException('Tidak ada target valid yang bisa diproses untuk campaign ini.');
            }

            $campaign = Campaign::create([
                'nama' => $payload['nama_campaign'],
                'tipe_input' => $inputType,
                'status' => Campaign::STATUS_DRAFT,
                'total' => 0,
                'terkirim' => 0,
                'gagal' => 0,
                'settings' => $settings,
            ]);
            $campaignCreated = true;

            $campaign = $this->campaignExecutor->queueCampaign(
                campaign: $campaign,
                targets: $parserResult['targets'],
                messageTemplate: $payload['bulking_pesan'],
                mediaPath: $mediaPath,
            );

            return [
                'campaign' => $campaign,
                'parser_result' => $parserResult,
                'media_path' => $mediaPath,
            ];
        } catch (Throwable $throwable) {
            if ($campaignCreated && $campaign) {
                $campaign->pesans()->delete();
                $campaign->delete();
            }

            if ($mediaPath) {
                Storage::delete($mediaPath);
            }

            throw $throwable;
        }
    }

    protected function parseSpreadsheetFile(?UploadedFile $targetFile): array
    {
        if (! $targetFile) {
            throw new BulkingTargetParserException('File Excel/CSV wajib dipilih.');
        }

        return $this->targetParser->parseSpreadsheet($targetFile);
    }
}
