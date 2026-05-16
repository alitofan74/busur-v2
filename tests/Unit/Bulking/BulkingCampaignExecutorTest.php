<?php

namespace Tests\Unit\Bulking;

use App\Models\Campaign;
use App\Services\Bulking\BulkingCampaignExecutor;
use App\Services\Bulking\BulkingMessageRenderer;
use Tests\TestCase;

class BulkingCampaignExecutorTest extends TestCase
{
    public function test_it_normalizes_campaign_settings_before_execution(): void
    {
        $campaign = new Campaign([
            'settings' => [
                'min_delay' => 30,
                'max_delay' => 10,
                'batch_size' => 0,
                'rest_after_batch' => -5,
                'retry_limit' => -1,
            ],
        ]);

        $settings = app(BulkingCampaignExecutor::class)->resolveSettings($campaign);

        $this->assertSame([
            'min_delay' => 30,
            'max_delay' => 30,
            'batch_size' => 1,
            'rest_after_batch' => 0,
            'retry_limit' => 0,
        ], $settings);
    }

    public function test_it_renders_message_placeholders_from_target_payload(): void
    {
        $renderer = app(BulkingMessageRenderer::class);

        $rendered = $renderer->render(
            'Halo {nama}, kota anda {kota}.',
            ['nama' => 'Budi', 'kota' => 'Malang']
        );

        $this->assertSame('Halo Budi, kota anda Malang.', $rendered);
    }

    public function test_it_renders_spintax_variations(): void
    {
        $renderer = app(BulkingMessageRenderer::class);

        $template = '{Halo|Hai} Budi';
        
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $renderer->render($template);
        }

        $this->assertContains('Halo Budi', $results);
        $this->assertContains('Hai Budi', $results);
    }
}
