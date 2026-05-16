<?php

namespace App\Services\Bulking;

use Illuminate\Support\Str;

class BulkingMessageRenderer
{
    public function render(string $template, array $placeholders = []): string
    {
        if ($template === '') {
            return $template;
        }

        $rendered = $template;

        // 1. Handle Spintax: {Halo|Hai|Salam}
        $rendered = $this->parseSpintax($rendered);

        // 2. Handle Placeholders: {nama}
        if ($placeholders !== []) {
            foreach ($placeholders as $key => $value) {
                $normalizedKey = Str::of((string) $key)
                    ->ascii()
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '_')
                    ->trim('_')
                    ->value();

                if ($normalizedKey === '') {
                    continue;
                }

                $rendered = str_replace(
                    '{' . $normalizedKey . '}',
                    (string) $value,
                    $rendered
                );
            }
        }

        return $rendered;
    }

    protected function parseSpintax(string $text): string
    {
        return preg_replace_callback('/\{([^{}|]+\|[^{}]*)\}/', function ($matches) {
            $choices = explode('|', $matches[1]);
            return $choices[array_rand($choices)];
        }, $text) ?? $text;
    }
}
