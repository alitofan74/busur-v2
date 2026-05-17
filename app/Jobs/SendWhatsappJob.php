<?php

namespace App\Jobs;

use App\Models\Pesan;
use App\Services\WhatsappMessageSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pesan;

    /**
     * Create a new job instance.
     *
     * @param Pesan $pesan
     */
    public function __construct(Pesan $pesan)
    {
        $this->pesan = $pesan;
    }

    /**
     * Execute the job.
     *
     * @param WhatsappMessageSender $messageSender
     * @return void
     */
    public function handle(WhatsappMessageSender $messageSender)
    {
        try {
            // Update status ke processing
            $this->pesan->update(['status' => Pesan::STATUS_PROCESSING]);

            $messageSender->send($this->pesan);

            // Sukses
            $this->pesan->update(['status' => Pesan::STATUS_SENT]);
        } catch (\Exception $e) {
            // Gagal
            $this->pesan->update([
                'status' => Pesan::STATUS_FAILED,
                'error_message' => $e->getMessage()
            ]);
            
            // Re-throw agar sistem queue tahu bahwa ini gagal (untuk retry)
            throw $e;
        }
    }
}
