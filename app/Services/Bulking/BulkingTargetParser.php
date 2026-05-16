<?php

namespace App\Services\Bulking;

use App\Services\Bulking\Exceptions\BulkingTargetParserException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ZipArchive;

class BulkingTargetParser
{
    protected const PHONE_HEADER_ALIASES = [
        'nomor',
        'nomor_hp',
        'nomor_whatsapp',
        'nomor_wa',
        'phone',
        'phone_number',
        'whatsapp',
        'wa',
        'mobile',
        'hp',
        'no_hp',
        'nohp',
        'no_wa',
    ];

    public function __construct(
        protected WhatsappNumberNormalizer $numberNormalizer = new WhatsappNumberNormalizer(),
    ) {
    }

    public function parseManual(string $input): array
    {
        $entries = Collection::make(preg_split('/[\r\n;]+/', $input) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values();

        if ($entries->isEmpty()) {
            throw new BulkingTargetParserException('Input manual belum berisi nomor WhatsApp.');
        }

        $targets = [];
        $invalidRows = [];

        foreach ($entries as $index => $value) {
            $rowNumber = $index + 1;
            $normalized = $this->numberNormalizer->normalize($value);

            if (! $normalized['valid']) {
                $invalidRows[] = [
                    'row_number' => $rowNumber,
                    'source' => 'manual',
                    'raw_number' => $value,
                    'errors' => [$normalized['error']],
                    'placeholders' => [],
                ];

                continue;
            }

            $targets[] = [
                'nomor' => $normalized['nomor'],
                'placeholders' => [],
                'row_number' => $rowNumber,
                'source' => 'manual',
            ];
        }

        return $this->buildResult(
            source: 'manual',
            targets: $targets,
            invalidRows: $invalidRows,
            totalRows: $entries->count(),
            placeholderKeys: [],
        );
    }

    public function parseSpreadsheet(UploadedFile|string $file): array
    {
        $fileInfo = $this->resolveFileInfo($file);
        $rows = match ($fileInfo['extension']) {
            'csv' => $this->readCsvRows($fileInfo['path']),
            'xlsx' => $this->readXlsxRows($fileInfo['path']),
            'xls' => throw new BulkingTargetParserException('File .xls belum didukung. Gunakan format .xlsx atau .csv.'),
            default => throw new BulkingTargetParserException('Format file tidak didukung. Gunakan .xlsx atau .csv.'),
        };

        if (count($rows) < 2) {
            throw new BulkingTargetParserException('File target harus memiliki header dan minimal satu baris data.');
        }

        $headerRow = array_shift($rows);
        $headerMap = $this->buildHeaderMap($headerRow);
        $phoneColumnIndex = $this->resolvePhoneColumnIndex($headerMap);

        $targets = [];
        $invalidRows = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $rawNumber = $row[$phoneColumnIndex] ?? '';
            $placeholders = $this->extractPlaceholders($row, $headerMap, $phoneColumnIndex);
            $normalized = $this->numberNormalizer->normalize((string) $rawNumber);

            if (! $normalized['valid']) {
                $invalidRows[] = [
                    'row_number' => $rowNumber,
                    'source' => 'excel',
                    'raw_number' => (string) $rawNumber,
                    'errors' => [$normalized['error']],
                    'placeholders' => $placeholders,
                ];

                continue;
            }

            $targets[] = [
                'nomor' => $normalized['nomor'],
                'placeholders' => $placeholders,
                'row_number' => $rowNumber,
                'source' => 'excel',
            ];
        }

        return $this->buildResult(
            source: 'excel',
            targets: $targets,
            invalidRows: $invalidRows,
            totalRows: count($rows),
            placeholderKeys: array_values(array_filter(
                array_column($headerMap, 'placeholder_key'),
                fn ($key) => $key !== null
            )),
        );
    }

    protected function resolveFileInfo(UploadedFile|string $file): array
    {
        if ($file instanceof UploadedFile) {
            $path = $file->getRealPath() ?: $file->path();
            $extension = strtolower($file->getClientOriginalExtension());
        } else {
            $path = (string) $file;
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }

        if ($path === '' || ! is_file($path)) {
            throw new BulkingTargetParserException('File target tidak ditemukan.');
        }

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new BulkingTargetParserException('Format file tidak valid. Gunakan .xlsx atau .csv.');
        }

        return [
            'path' => $path,
            'extension' => $extension,
        ];
    }

    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new BulkingTargetParserException('File CSV tidak dapat dibaca.');
        }

        $rows = [];

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null]) {
                    continue;
                }

                $normalizedRow = array_map(
                    fn ($value) => is_string($value) ? trim($value) : $value,
                    $row
                );

                if ($rows === [] && isset($normalizedRow[0])) {
                    $normalizedRow[0] = $this->stripUtf8Bom((string) $normalizedRow[0]);
                }

                $rows[] = $normalizedRow;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    protected function readXlsxRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new BulkingTargetParserException('Pembacaan file .xlsx memerlukan ekstensi ZIP pada PHP.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new BulkingTargetParserException('File .xlsx tidak dapat dibuka.');
        }

        try {
            $worksheetPath = $this->findFirstWorksheetPath($zip);
            $worksheetXml = $worksheetPath ? $zip->getFromName($worksheetPath) : false;

            if ($worksheetXml === false) {
                throw new BulkingTargetParserException('Worksheet pada file .xlsx tidak ditemukan.');
            }

            $sharedStrings = $this->readSharedStrings($zip);
            $sheet = simplexml_load_string($worksheetXml);

            if ($sheet === false || ! isset($sheet->sheetData)) {
                throw new BulkingTargetParserException('Worksheet .xlsx tidak memiliki struktur yang valid.');
            }

            $rows = [];

            foreach ($sheet->sheetData->row as $row) {
                $cells = [];

                foreach ($row->c as $cell) {
                    $reference = (string) $cell['r'];
                    $columnIndex = $this->columnReferenceToIndex($reference);
                    $cells[$columnIndex] = $this->extractCellValue($cell, $sharedStrings);
                }

                if ($cells === []) {
                    continue;
                }

                $maxIndex = max(array_keys($cells));
                $denseRow = [];

                for ($index = 0; $index <= $maxIndex; $index++) {
                    $denseRow[$index] = array_key_exists($index, $cells)
                        ? trim((string) $cells[$index])
                        : '';
                }

                $rows[] = $denseRow;
            }

            if ($rows !== [] && isset($rows[0][0])) {
                $rows[0][0] = $this->stripUtf8Bom((string) $rows[0][0]);
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    protected function findFirstWorksheetPath(ZipArchive $zip): ?string
    {
        $worksheetPaths = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                $worksheetPaths[] = $name;
            }
        }

        sort($worksheetPaths);

        return $worksheetPaths[0] ?? null;
    }

    protected function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $sharedStrings = [];

        foreach ($document->si as $stringItem) {
            $textParts = [];

            if (isset($stringItem->t)) {
                $textParts[] = (string) $stringItem->t;
            }

            foreach ($stringItem->r as $run) {
                $textParts[] = (string) $run->t;
            }

            $sharedStrings[] = implode('', $textParts);
        }

        return $sharedStrings;
    }

    protected function extractCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            $sharedStringIndex = (int) ($cell->v ?? 0);

            return (string) ($sharedStrings[$sharedStringIndex] ?? '');
        }

        if ($type === 'inlineStr') {
            $parts = [];

            if (isset($cell->is->t)) {
                $parts[] = (string) $cell->is->t;
            }

            foreach ($cell->is->r as $run) {
                $parts[] = (string) $run->t;
            }

            return implode('', $parts);
        }

        return (string) ($cell->v ?? '');
    }

    protected function columnReferenceToIndex(string $reference): int
    {
        if (! preg_match('/([A-Z]+)/', strtoupper($reference), $matches)) {
            return 0;
        }

        $letters = $matches[1];
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    protected function buildHeaderMap(array $headerRow): array
    {
        $headerMap = [];
        $usedPlaceholderKeys = [];

        foreach ($headerRow as $index => $headerValue) {
            $header = trim((string) $headerValue);
            $normalizedHeader = $this->normalizeHeaderKey($header);

            if ($normalizedHeader === '') {
                continue;
            }

            $isPhoneColumn = in_array($normalizedHeader, self::PHONE_HEADER_ALIASES, true);
            $placeholderKey = $isPhoneColumn
                ? null
                : $this->makeUniquePlaceholderKey($normalizedHeader, $usedPlaceholderKeys);

            $headerMap[$index] = [
                'original_header' => $header,
                'normalized_header' => $normalizedHeader,
                'placeholder_key' => $placeholderKey,
                'is_phone_column' => $isPhoneColumn,
            ];
        }

        if ($headerMap === []) {
            throw new BulkingTargetParserException('Header file target kosong atau tidak valid.');
        }

        return $headerMap;
    }

    protected function resolvePhoneColumnIndex(array $headerMap): int
    {
        foreach ($headerMap as $index => $header) {
            if ($header['is_phone_column']) {
                return $index;
            }
        }

        throw new BulkingTargetParserException(
            'Header nomor utama tidak ditemukan. Gunakan salah satu kolom: nomor, phone, whatsapp, atau wa.'
        );
    }

    protected function extractPlaceholders(array $row, array $headerMap, int $phoneColumnIndex): array
    {
        $placeholders = [];

        foreach ($headerMap as $index => $header) {
            if ($index === $phoneColumnIndex || $header['placeholder_key'] === null) {
                continue;
            }

            $placeholders[$header['placeholder_key']] = trim((string) ($row[$index] ?? ''));
        }

        return $placeholders;
    }

    protected function makeUniquePlaceholderKey(string $baseKey, array &$usedKeys): string
    {
        $candidate = $baseKey;
        $suffix = 2;

        while (in_array($candidate, $usedKeys, true)) {
            $candidate = $baseKey . '_' . $suffix;
            $suffix++;
        }

        $usedKeys[] = $candidate;

        return $candidate;
    }

    protected function normalizeHeaderKey(string $header): string
    {
        $normalized = Str::of($header)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        return $normalized;
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function stripUtf8Bom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    protected function buildResult(
        string $source,
        array $targets,
        array $invalidRows,
        int $totalRows,
        array $placeholderKeys,
    ): array {
        return [
            'targets' => array_values($targets),
            'invalid_rows' => array_values($invalidRows),
            'summary' => [
                'source' => $source,
                'total_rows' => $totalRows,
                'valid_rows' => count($targets),
                'invalid_rows' => count($invalidRows),
                'placeholder_keys' => array_values(array_unique($placeholderKeys)),
            ],
        ];
    }
}
