<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendPesanTunggalRequest;
use App\Services\WhatsappService;
use App\Models\Pesan;
use App\Jobs\SendWhatsappJob;
use Illuminate\Http\Request;

class PesanTunggalController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsappService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $connection = $this->getConnectionStatus();
        return view('pesan.tunggal', compact('connection'));
    }

    public function store(SendPesanTunggalRequest $request)
    {
        try {
            // Cegah pengiriman jika ada campaign bulking yang sedang berjalan
            $activeCampaign = \App\Models\Campaign::whereIn('status', ['queued', 'running', 'resting'])->first();
            if ($activeCampaign) {
                throw new \Exception('Sistem tidak dapat mengirim pesan tunggal karena saat ini sedang memproses campaign bulking "' . $activeCampaign->nama . '".');
            }

            $mediaPath = null;
            
            // Jika ada file, simpan ke storage lokal dulu agar bisa dibaca oleh Job
            if ($request->hasFile('media')) {
                $mediaPath = $request->file('media')->store('temp_whatsapp');
            }

            // Simpan ke database riwayat pesan
            $pesan = Pesan::create([
                'nomor' => $request->nomor,
                'pesan' => $request->pesan,
                'media_path' => $mediaPath,
                'status' => 'pending'
            ]);

            // Kirim ke antrean background
            SendWhatsappJob::dispatch($pesan);

            $message = 'Pesan sedang diproses di background. Anda dapat melihat statusnya di menu Log Pesan.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pesan: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Gagal memproses pesan: ' . $e->getMessage()])->withInput();
        }
    }

    public function checkNumber(Request $request)
    {
        $request->validate(['number' => 'required']);
        
        try {
            $result = $this->whatsapp->checkNumber($request->number);
            
            return response()->json([
                'exists' => $result['registered'] ?? false, 
                'message' => ($result['registered'] ?? false) ? 'Nomor terdaftar di WhatsApp' : 'Nomor tidak terdaftar di WhatsApp'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false, 
                'message' => 'Gagal mengecek nomor: ' . $e->getMessage()
            ], 422);
        }
    }

    protected function getConnectionStatus()
    {
        try {
            $conn = $this->whatsapp->connection();
            if ($conn['status'] === 'ready') {
                return [
                    'connected' => true,
                    'status' => 'Terhubung',
                    'number' => $conn['info']['wid']['user'] ?? 'N/A'
                ];
            }
        } catch (\Exception $e) {}

        return [
            'connected' => false,
            'status' => 'Terputus',
            'number' => 'N/A'
        ];
    }

    public function log()
    {
        $pesans = Pesan::whereNull('campaign_id')
            ->latest()
            ->get();

        return view('pesan.tunggal-log', compact('pesans'));
    }
}
