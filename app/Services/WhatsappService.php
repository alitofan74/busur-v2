<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class WhatsappService
{
    protected PendingRequest $http;

    public function __construct()
    {
        $baseUrl = config('whatsapp.base_url');

        $this->http = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout(config('whatsapp.timeout'));

        if ($token = config('whatsapp.token')) {
            $this->http = $this->http->withToken($token);
        }

        if ($headers = config('whatsapp.headers', [])) {
            $this->http = $this->http->withHeaders($headers);
        }
    }

    protected function safeResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        throw new \RuntimeException(
            $response->body() ?: 'WhatsApp service request failed',
            $response->status()
        );
    }

    public function health(): array
    {
        return $this->safeResponse($this->http->get('/health'));
    }

    public function connection(): array
    {
        return $this->safeResponse($this->http->get('/connection'));
    }

    public function sendMessage(string $number, string $message): array
    {
        return $this->safeResponse($this->http->post('/send-message', [
            'number' => $number,
            'message' => $message,
        ]));
    }

    public function sendMedia(string $number, UploadedFile|string $file, ?string $message = null): array
    {
        if ($file instanceof UploadedFile) {
            $filename = $file->getClientOriginalName();
            $contents = file_get_contents($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $filename = basename($file);
            $contents = file_get_contents($file);
        } else {
            throw new \InvalidArgumentException('File must be an UploadedFile or a valid file path.');
        }

        return $this->safeResponse(
            $this->http
                ->timeout(60) // Perpanjang timeout khusus untuk media
                ->attach('file', $contents, $filename)
                ->post('/send-media', [
                    'number' => $number,
                    'message' => $message ?? '',
                ])
        );
    }

    public function checkNumber(string $number): array
    {
        return $this->safeResponse($this->http->get('/check-number', [
            'number' => $number,
        ]));
    }
}
