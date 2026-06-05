<?php

namespace Tests\Unit;

use App\Services\KodeReksaDanaParser;
use PHPUnit\Framework\TestCase;

class KodeReksaDanaParserTest extends TestCase
{
    public function test_it_parses_class_and_currency_from_absolute_positions(): void
    {
        $parser = new KodeReksaDanaParser();

        $this->assertSame('00', $parser->parseClassCode('BZ002C000BUBA001'));
        $this->assertSame('Tidak Ada', $parser->parseClass('BZ002C000BUBA001'));
        $this->assertSame('1', $parser->parseCurrencyCode('BZ002C000BUBA001'));
        $this->assertSame('USD', $parser->parseCurrency('BZ002C000BUBA001'));
    }

    public function test_it_returns_default_values_for_short_codes(): void
    {
        $parser = new KodeReksaDanaParser();

        $this->assertNull($parser->parseClassCode('BZ002C000BCNB0'));
        $this->assertSame('-', $parser->parseClass('BZ002C000BCNB0'));
        $this->assertNull($parser->parseCurrencyCode('BZ002C000BCNB0'));
        $this->assertSame('-', $parser->parseCurrency('BZ002C000BCNB0'));
    }

    public function test_parser_result_overrides_inconsistent_stored_values(): void
    {
        $parser = new KodeReksaDanaParser();

        $this->assertSame('Tidak Ada', $parser->resolveClassName('Kelas Z', 'BZ002C000BUBA001'));
        $this->assertSame('USD', $parser->resolveCurrencyName('IDR', 'BZ002C000BUBA001'));
    }
}
