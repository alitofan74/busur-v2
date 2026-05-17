<?php

namespace Tests\Unit\Bulking;

use App\Services\Bulking\BulkingTargetParser;
use App\Services\Bulking\Exceptions\BulkingTargetParserException;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BulkingTargetParserTest extends TestCase
{
    public function test_it_parses_manual_numbers_into_a_uniform_target_payload(): void
    {
        $parser = new BulkingTargetParser();

        $result = $parser->parseManual("081234567890;\n+62 812-1111-2222\nabc");

        $this->assertCount(2, $result['targets']);
        $this->assertSame('6281234567890', $result['targets'][0]['nomor']);
        $this->assertSame('6281211112222', $result['targets'][1]['nomor']);
        $this->assertSame('manual', $result['targets'][0]['source']);
        $this->assertSame([], $result['targets'][0]['placeholders']);

        $this->assertCount(1, $result['invalid_rows']);
        $this->assertSame(3, $result['invalid_rows'][0]['row_number']);
        $this->assertSame('abc', $result['invalid_rows'][0]['raw_number']);
    }

    public function test_it_throws_when_manual_input_is_empty(): void
    {
        $this->expectException(BulkingTargetParserException::class);

        (new BulkingTargetParser())->parseManual(" \n ; ");
    }

    public function test_it_parses_csv_targets_and_maps_placeholders_from_headers(): void
    {
        $parser = new BulkingTargetParser();
        $path = $this->createTempCsv([
            ['nomor', 'nama', 'kota'],
            ['081234567890', 'Budi', 'Malang'],
            ['abc', 'Sari', 'Surabaya'],
        ]);

        try {
            $result = $parser->parseSpreadsheet($path);
        } finally {
            File::delete($path);
        }

        $this->assertCount(1, $result['targets']);
        $this->assertSame('6281234567890', $result['targets'][0]['nomor']);
        $this->assertSame([
            'nama' => 'Budi',
            'kota' => 'Malang',
        ], $result['targets'][0]['placeholders']);
        $this->assertSame(2, $result['targets'][0]['row_number']);

        $this->assertCount(1, $result['invalid_rows']);
        $this->assertSame(3, $result['invalid_rows'][0]['row_number']);
        $this->assertSame([
            'nama',
            'kota',
        ], $result['summary']['placeholder_keys']);
    }

    public function test_it_requires_a_phone_header_in_csv_files(): void
    {
        $parser = new BulkingTargetParser();
        $path = $this->createTempCsv([
            ['nama', 'kota'],
            ['Budi', 'Malang'],
        ]);

        try {
            $this->expectException(BulkingTargetParserException::class);
            $parser->parseSpreadsheet($path);
        } finally {
            File::delete($path);
        }
    }

    protected function createTempCsv(array $rows): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'bulking-parser-');
        $path = $temporaryPath . '.csv';
        rename($temporaryPath, $path);

        $handle = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
