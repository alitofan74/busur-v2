<?php

namespace App\Http\Controllers;

use App\Services\WhatsappService;
use Illuminate\Http\Request;

class BulkingController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsappService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $connection = $this->getConnectionStatus();
        return view('pesan.bulking', compact('connection'));
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
}
