<?php

namespace Tests\Unit;

use App\Services\KodeReksaDanaParser;
use PHPUnit\Framework\TestCase;

class KodeReksaDanaParserTest extends TestCase
{
    // DR002D000AASRA000 = Kode MI DR002, Jenis D (Saham), Kategori 0 (Konv), Index 0, ETF 0, Nama AASR, Kelas A00, Mata Uang 0 (IDR)
    public function test_it_parses_class_and_currency_from_absolute_positions(): void
    {
        $parser = new KodeReksaDanaParser();

        // 17-char: pos 14-16 = kelas, pos 17 = mata uang
        $this->assertSame('A00', $parser->parseClassCode('DR002D000AASRA000'));
        $this->assertSame('Kelas A', $parser->parseClass('DR002D000AASRA000'));
        $this->assertSame('0', $parser->parseCurrencyCode('DR002D000AASRA000'));
        $this->assertSame('IDR', $parser->parseCurrency('DR002D000AASRA000'));

        // USD test: BZ002C000BUBA0001
        $this->assertSame('000', $parser->parseClassCode('BZ002C000BUBA0001'));
        $this->assertSame('Tidak Ada', $parser->parseClass('BZ002C000BUBA0001'));
        $this->assertSame('1', $parser->parseCurrencyCode('BZ002C000BUBA0001'));
        $this->assertSame('USD', $parser->parseCurrency('BZ002C000BUBA0001'));
    }

    public function test_it_returns_default_values_for_short_codes(): void
    {
        $parser = new KodeReksaDanaParser();

        // 14 chars - too short
        $this->assertNull($parser->parseClassCode('BZ002C000BCNB0'));
        $this->assertSame('-', $parser->parseClass('BZ002C000BCNB0'));
        $this->assertNull($parser->parseCurrencyCode('BZ002C000BCNB0'));
        $this->assertSame('-', $parser->parseCurrency('BZ002C000BCNB0'));
    }

    public function test_parser_result_overrides_inconsistent_stored_values(): void
    {
        $parser = new KodeReksaDanaParser();

        // 17-char code: DR002D000AASRA000 → Kelas A, IDR
        $this->assertSame('Kelas A', $parser->resolveClassName('Kelas Z', 'DR002D000AASRA000'));
        $this->assertSame('IDR', $parser->resolveCurrencyName('USD', 'DR002D000AASRA000'));
    }
}
