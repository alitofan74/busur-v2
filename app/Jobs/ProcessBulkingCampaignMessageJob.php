<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Pesan;
use App\Services\Bulking\BulkingCampaignExecutor;
use App\Services\WhatsappMessageSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessBulkingCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $campaignId,
        protected int $pesanId,
        protected array $settings,
    ) {
    }

    public function tries(): int
    {
        return max(1, ((int) ($this->settings['retry_limit'] ?? 0)) + 1);
    }

    public function backoff(): int
    {
        return max(1, (int) ($this->settings['min_delay'] ?? 1));
    }

    public function handle(
        WhatsappMessageSender $messageSender,
        BulkingCampaignExecutor $campaignExecutor,
    ): void {
        $campaign = Campaign::query()->find($this->campaignId);

        if (! $campaign || in_array($campaign->status, [Campaign::STATUS_COMPLETED, Campaign::STATUS_FAILED], true)) {
            return;
        }

        if ($campaign->status === Campaign::STATUS_PAUSED) {
            return;
        }

        $pesan = Pesan::query()
            ->whereKey($this->pesanId)
            ->where('campaign_id', $campaign->getKey())
            ->first();

        if (! $pesan || in_array($pesan->status, [Pesan::STATUS_SENT, Pesan::STATUS_FAILED], true)) {
            return;
        }

        $campaign->forceFill([
            'status' => Campaign::STATUS_RUNNING,
            'started_at' => $campaign->started_at ?? now(),
        ])->save();

        $pesan->forceFill([
            'status' => Pesan::STATUS_PROCESSING,
            'error_message' => null,
        ])->save();

        try {
            $messageSender->send($pesan);

            $pesan->forceFill([
                'status' => Pesan::STATUS_SENT,
                'error_message' => null,
            ])->save();

            $campaign->increment('terkirim', 1, [
                'last_processed_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            if ($this->attempts() <= (int) ($this->settings['retry_limit'] ?? 0)) {
                $pesan->forceFill([
                    'status' => Pesan::STATUS_PROCESSING,
                    'error_message' => $throwable->getMessage(),
                ])->save();

                throw $throwable;
            }

            $pesan->forceFill([
                'status' => Pesan::STATUS_FAILED,
                'error_message' => $throwable->getMessage(),
            ])->save();

            $campaign->increment('gagal', 1, [
                'last_processed_at' => now(),
            ]);
        }

        $campaignExecutor->dispatchNextPendingMessage($campaign->fresh(), $this->settings);
    }

    public function failed(Throwable $throwable): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        app(BulkingCampaignExecutor::class)->markCampaignFailed($campaign);
    }
}
