<?php

namespace App\Jobs;

use App\Models\Pesan;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
     * @param WhatsappService $whatsapp
     * @return void
     */
    public function handle(WhatsappService $whatsapp)
    {
        try {
            // Update status ke processing
            $this->pesan->update(['status' => 'processing']);

            if ($this->pesan->media_path) {
                $filePath = Storage::path($this->pesan->media_path);
                
                if (file_exists($filePath)) {
                    $whatsapp->sendMedia(
                        $this->pesan->nomor,
                        $filePath,
                        $this->pesan->pesan
                    );
                } else {
                    throw new \Exception("File media tidak ditemukan di: " . $filePath);
                }
            } else {
                $whatsapp->sendMessage($this->pesan->nomor, $this->pesan->pesan);
            }

            // Sukses
            $this->pesan->update(['status' => 'sent']);
        } catch (\Exception $e) {
            // Gagal
            $this->pesan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            // Re-throw agar sistem queue tahu bahwa ini gagal (untuk retry)
            throw $e;
        }
    }
}
