<?php

namespace App\Services;

use App\Models\Pesan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WhatsappMessageSender
{
    public function __construct(
        protected WhatsappService $whatsapp,
    ) {
    }

    public function send(Pesan $pesan): void
    {
        if ($pesan->media_path) {
            $filePath = Storage::path($pesan->media_path);

            if (! file_exists($filePath)) {
                throw new RuntimeException('File media tidak ditemukan di: ' . $filePath);
            }

            $this->whatsapp->sendMedia(
                $pesan->nomor,
                $filePath,
                $pesan->pesan
            );

            return;
        }

        $this->whatsapp->sendMessage($pesan->nomor, $pesan->pesan);
    }
}
