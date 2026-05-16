<?php

namespace App\Services\Bulking;

use Illuminate\Support\Str;

class BulkingMessageRenderer
{
    public function render(string $template, array $placeholders = []): string
    {
        if ($template === '' || $placeholders === []) {
            return $template;
        }

        $rendered = $template;

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

        return $rendered;
    }
}
