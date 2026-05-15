<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle the incoming WhatsApp service webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        // Validate Webhook Secret
        $secret = $request->header('X-Webhook-Secret');
        if ($secret !== config('whatsapp.webhook_secret')) {

            Log::warning("Unauthorized WhatsApp Webhook attempt", [
                'header_secret' => $secret,
                'ip' => $request->ip()
            ]);
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;

        $data = $payload['data'] ?? [];

        Log::info("WhatsApp Webhook Received: {$event}", $payload);

        // Map events to our internal qrData structure
        $qrData = [
            'status' => 'Belum Terhubung',
            'session' => $payload['clientId'] ?? 'WA-SERVICE',
            'phone' => 'N/A',
            'last_updated' => now()->format('d M Y H:i'),
            'expires_in' => '5 menit',
            'qr_image' => null,
            'scan_instructions' => [
                'Buka WhatsApp di ponsel Anda.',
                'Pilih menu Linked Devices / WhatsApp Web.',
                'Pindai QR code yang tampil di layar.',
                'Tunggu sampai status berubah menjadi Connected.'
            ],
            'terms' => [
                'Gunakan nomor WhatsApp resmi yang valid.',
                'QR code berlaku sementara dan dapat kadaluarsa.',
                'Jangan bagikan QR code dengan pihak lain.'
            ],
        ];

        switch ($event) {
            case 'qr':
                $qrData['status'] = 'Menunggu Scan';
                $qrData['qr_image'] = $data['qr'] ?? null;
                break;

            case 'ready':
                $qrData['status'] = 'Terhubung';
                $qrData['phone'] = $data['info']['wid']['user'] ?? 'N/A';
                $qrData['expires_in'] = 'Aktif';
                break;

            case 'authenticated':
                $qrData['status'] = 'Menunggu Inisialisasi';
                break;

            case 'disconnected':
            case 'auth_failure':
                $qrData['status'] = 'Terputus';
                break;

            case 'state_change':
                if (in_array($data['state'] ?? '', ['DISCONNECTED', 'CONFLICT', 'UNPAIRED', 'UNLAUNCHED'])) {
                    $qrData['status'] = 'Terputus';
                }
                break;


            default:
                // Keep existing cache if unknown event (like 'message')
                return response()->json(['status' => 'ignored']);
        }

        // Update the cache for 24 hours (or until next webhook)
        Cache::put('wa_status_default', $qrData, now()->addDay());

        return response()->json(['status' => 'success']);
    }
}
