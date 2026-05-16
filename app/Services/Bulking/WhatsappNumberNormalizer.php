<?php

namespace App\Services\Bulking;

class WhatsappNumberNormalizer
{
    public function normalize(?string $rawNumber): array
    {
        $rawNumber = trim((string) $rawNumber);

        if ($rawNumber === '') {
            return $this->invalidResult($rawNumber, 'Nomor WhatsApp kosong.');
        }

        $digits = preg_replace('/\D+/', '', $rawNumber) ?? '';

        if ($digits === '') {
            return $this->invalidResult($rawNumber, 'Nomor WhatsApp tidak mengandung angka yang valid.');
        }

        if (str_starts_with($digits, '620')) {
            $digits = '62' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return $this->invalidResult($rawNumber, 'Nomor WhatsApp harus terdiri dari 10 sampai 15 digit.');
        }

        if (! preg_match('/^[1-9]\d+$/', $digits)) {
            return $this->invalidResult($rawNumber, 'Nomor WhatsApp mengandung format yang tidak didukung.');
        }

        return [
            'valid' => true,
            'nomor' => $digits,
            'raw' => $rawNumber,
            'error' => null,
        ];
    }

    protected function invalidResult(string $rawNumber, string $message): array
    {
        return [
            'valid' => false,
            'nomor' => null,
            'raw' => $rawNumber,
            'error' => $message,
        ];
    }
}
