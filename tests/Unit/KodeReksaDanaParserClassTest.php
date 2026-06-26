<?php

namespace Tests\Unit;

use App\Models\InvestmentManager;
use App\Services\KodeReksaDanaParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KodeReksaDanaParserClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_extract_kelas_from_nama(): void
    {
        $this->assertSame('Kelas A', KodeReksaDanaParser::extractKelasFromNama('Allianz Alpha Sector Rotation Kelas A'));
        $this->assertSame('Kelas A1', KodeReksaDanaParser::extractKelasFromNama('RD Beta Kelas A1'));
        $this->assertSame('Kelas A1K', KodeReksaDanaParser::extractKelasFromNama('RD Gamma Kelas A1K'));
        $this->assertSame('Kelas B', KodeReksaDanaParser::extractKelasFromNama('RD Delta Kelas B'));
        $this->assertSame('Kelas C', KodeReksaDanaParser::extractKelasFromNama('RD Epsilon Kelas C'));
        $this->assertNull(KodeReksaDanaParser::extractKelasFromNama('RD Zeta'));
    }

    public function test_abbreviate_nama_ignores_kelas(): void
    {
        $this->assertSame('AASR', KodeReksaDanaParser::abbreviateNama('Allianz Alpha Sector Rotation Kelas A'));
        // Satu kata 3 huruf: huruf depan (sekaligus terakhir) diulang sampai 4 karakter
        $this->assertSame('AAAA', KodeReksaDanaParser::abbreviateNama('ABC Kelas A'));
    }

    public function test_attributes_from_kode_or_nama(): void
    {
        InvestmentManager::create([
            'name' => 'Allianz',
            'kode_mi' => 'DR002',
        ]);

        $parser = app(KodeReksaDanaParser::class);

        // Dari kode valid
        $attrs = $parser->attributesFromKodeOrNama('DR002D000AASRA000', 'Sembarang');
        $this->assertSame('Saham', $attrs['jenis']);
        $this->assertSame('Konvensional', $attrs['kategori_produk']);
        $this->assertSame('Kelas A', $attrs['kelas']);
        $this->assertSame('IDR', $attrs['mata_uang']);

        // Dari nama jika kode kosong
        $attrs = $parser->attributesFromKodeOrNama(null, 'RD Beta Kelas B');
        $this->assertSame('Kelas B', $attrs['kelas']);
        $this->assertArrayNotHasKey('jenis', $attrs);
    }
}
