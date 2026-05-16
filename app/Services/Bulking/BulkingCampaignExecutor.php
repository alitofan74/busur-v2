<?php

namespace App\Services\Bulking;

use App\Jobs\ProcessBulkingCampaignMessageJob;
use App\Models\Campaign;
use App\Models\Pesan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BulkingCampaignExecutor
{
    protected const DEFAULT_SETTINGS = [
        'min_delay' => 10,
        'max_delay' => 30,
        'batch_size' => 10,
        'rest_after_batch' => 120,
        'retry_limit' => 0,
    ];

    public function __construct(
        protected BulkingMessageRenderer $messageRenderer = new BulkingMessageRenderer(),
    ) {
    }

    public function queueCampaign(
        Campaign $campaign,
        array $targets,
        string $messageTemplate,
        ?string $mediaPath = null,
    ): Campaign {
        if ($targets === []) {
            throw new RuntimeException('Campaign bulking tidak memiliki target valid untuk diproses.');
        }

        if ($campaign->pesans()->exists()) {
            throw new RuntimeException('Campaign ini sudah memiliki antrean pesan dan tidak boleh di-bootstrap ulang.');
        }

        $settings = $this->resolveSettings($campaign);
        $preparedTargets = $this->prepareTargets($targets, $messageTemplate, $mediaPath);

        DB::transaction(function () use ($campaign, $preparedTargets, $settings) {
            $campaign->forceFill([
                'tipe_input' => $this->resolveInputType($preparedTargets),
                'total' => count($preparedTargets),
                'terkirim' => 0,
                'gagal' => 0,
                'status' => Campaign::STATUS_QUEUED,
                'settings' => array_merge($campaign->settings ?? [], $settings),
                'started_at' => null,
                'finished_at' => null,
                'last_processed_at' => null,
            ])->save();

            foreach ($preparedTargets as $target) {
                $campaign->pesans()->create([
                    'nomor' => $target['nomor'],
                    'pesan' => $target['pesan'],
                    'media_path' => $target['media_path'],
                    'status' => Pesan::STATUS_PENDING,
                    'error_message' => null,
                ]);
            }
        });

        $firstMessage = $campaign->fresh()
            ->pesans()
            ->where('status', Pesan::STATUS_PENDING)
            ->orderBy('id')
            ->first();

        if (! $firstMessage) {
            throw new RuntimeException('Campaign tidak memiliki pesan awal untuk dijadwalkan.');
        }

        ProcessBulkingCampaignMessageJob::dispatch(
            campaignId: $campaign->getKey(),
            pesanId: $firstMessage->getKey(),
            settings: $settings,
        )->delay(now()->addSeconds($this->randomDelaySeconds($settings)));

        return $campaign->fresh();
    }

    public function resolveSettings(Campaign $campaign): array
    {
        $settings = array_merge(self::DEFAULT_SETTINGS, $campaign->settings ?? []);

        $minDelay = max(0, (int) ($settings['min_delay'] ?? self::DEFAULT_SETTINGS['min_delay']));
        $maxDelay = max($minDelay, (int) ($settings['max_delay'] ?? self::DEFAULT_SETTINGS['max_delay']));
        $batchSize = max(1, (int) ($settings['batch_size'] ?? self::DEFAULT_SETTINGS['batch_size']));
        $restAfterBatch = max(0, (int) ($settings['rest_after_batch'] ?? self::DEFAULT_SETTINGS['rest_after_batch']));
        $retryLimit = max(0, (int) ($settings['retry_limit'] ?? self::DEFAULT_SETTINGS['retry_limit']));

        return [
            'min_delay' => $minDelay,
            'max_delay' => $maxDelay,
            'batch_size' => $batchSize,
            'rest_after_batch' => $restAfterBatch,
            'retry_limit' => $retryLimit,
        ];
    }

    public function dispatchNextPendingMessage(Campaign $campaign, array $settings): void
    {
        $campaign->refresh();

        $nextMessage = $campaign->pesans()
            ->where('status', Pesan::STATUS_PENDING)
            ->orderBy('id')
            ->first();

        if (! $nextMessage) {
            $campaign->forceFill([
                'status' => Campaign::STATUS_COMPLETED,
                'finished_at' => now(),
            ])->save();

            return;
        }

        $processedCount = $campaign->terkirim + $campaign->gagal;
        $shouldRest = $processedCount > 0
            && ($processedCount % max(1, (int) $settings['batch_size']) === 0);

        $delaySeconds = $shouldRest
            ? max(0, (int) $settings['rest_after_batch'])
            : $this->randomDelaySeconds($settings);

        $campaign->forceFill([
            'status' => $shouldRest ? Campaign::STATUS_RESTING : Campaign::STATUS_QUEUED,
        ])->save();

        ProcessBulkingCampaignMessageJob::dispatch(
            campaignId: $campaign->getKey(),
            pesanId: $nextMessage->getKey(),
            settings: $settings,
        )->delay(now()->addSeconds($delaySeconds));
    }

    public function markCampaignFailed(Campaign $campaign): void
    {
        $campaign->forceFill([
            'status' => Campaign::STATUS_FAILED,
            'finished_at' => now(),
        ])->save();
    }

    public function randomDelaySeconds(array $settings): int
    {
        $minDelay = max(0, (int) ($settings['min_delay'] ?? self::DEFAULT_SETTINGS['min_delay']));
        $maxDelay = max($minDelay, (int) ($settings['max_delay'] ?? self::DEFAULT_SETTINGS['max_delay']));

        return random_int($minDelay, $maxDelay);
    }

    protected function prepareTargets(array $targets, string $messageTemplate, ?string $mediaPath): array
    {
        return array_map(function (array $target) use ($messageTemplate, $mediaPath) {
            $placeholders = is_array($target['placeholders'] ?? null)
                ? $target['placeholders']
                : [];

            return [
                'nomor' => (string) $target['nomor'],
                'pesan' => $this->messageRenderer->render($messageTemplate, $placeholders),
                'media_path' => $mediaPath,
                'source' => $target['source'] ?? Campaign::INPUT_MANUAL,
            ];
        }, $targets);
    }

    protected function resolveInputType(array $preparedTargets): string
    {
        $source = $preparedTargets[0]['source'] ?? Campaign::INPUT_MANUAL;

        return $source === Campaign::INPUT_EXCEL
            ? Campaign::INPUT_EXCEL
            : Campaign::INPUT_MANUAL;
    }
}
