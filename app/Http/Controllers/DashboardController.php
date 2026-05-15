<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WhatsappService;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(WhatsappService $whatsapp)
    {
        $qrData = cache()->remember('wa_status_default', 3600, function() use ($whatsapp) {
            return $this->getQrData($whatsapp);
        });
        return view('dashboard.index', compact('qrData'));
    }

    /**
     * Get current WhatsApp connection status and QR data as JSON.
     *
     * @param WhatsappService $whatsapp
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(WhatsappService $whatsapp)
    {
        $qrData = cache()->remember('wa_status_default', 3600, function() use ($whatsapp) {
            return $this->getQrData($whatsapp);
        });
        return response()->json($qrData);
    }



    /**
     * Helper to fetch and format WhatsApp connection data.
     *
     * @param WhatsappService $whatsapp
     * @return array
     */
    protected function getQrData(WhatsappService $whatsapp): array
    {
        $qrData = [
            'status' => 'Belum Terhubung',
            'session' => 'WA-SERVICE-1234',
            'phone' => '+62 812-3456-7890',
            'last_updated' => '14 Mei 2026 09:35',
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

        try {
            $waconnection = $whatsapp->connection();

            if ($waconnection['status'] === 'ready') {
                $qrData['status'] = 'Terhubung';
                $qrData['session'] = $waconnection['info']['wid']['user'] ?? 'N/A';
                $qrData['phone'] = $waconnection['info']['wid']['user'] ?? 'N/A';
                $qrData['last_updated'] = now()->format('d M Y H:i');
                $qrData['expires_in'] = 'Aktif';
            } elseif ($waconnection['status'] === 'qr' && isset($waconnection['qr'])) {
                $qrData['status'] = 'Menunggu Scan';
                $qrData['qr_image'] = $waconnection['qr'];
                $qrData['last_updated'] = now()->format('d M Y H:i');
                $qrData['expires_in'] = '5 menit';
            } elseif ($waconnection['status'] === 'pending') {
                $qrData['status'] = 'Menunggu Inisialisasi';
            }
        } catch (\Exception $e) {
            $qrData['status'] = 'Service Tidak Tersedia';
        }

        return $qrData;
    }

    public function refreshQr(WhatsappService $whatsapp)
    {
        // Clear cache so we get fresh data immediately
        cache()->forget('wa_status_default');

        // Trigger refresh QR by calling connection (assuming WA service handles refresh internally)
        try {
            $whatsapp->connection();
        } catch (\Exception $e) {
            // Ignore errors for refresh
        }

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard');
    }




}
