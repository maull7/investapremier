<?php

namespace Tests\Feature\Imports;

use App\Imports\HargaReksaDanaImport;
use App\Models\InvestmentManager;
use App\Models\ReksaDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class HargaReksaDanaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_counts_unique_records_and_detects_duplicates(): void
    {
        InvestmentManager::create([
            'name' => 'Manajer Test',
            'kode_mi' => 'BHD02',
        ]);

        $csv = <<<CSV
kode_reksa_dana,nama_reksa_dana,nama_manajer_investasi,jenis,kategori,kategori_produk,mata_uang,nab_per_unit,tanggal_nab
BHD02D0000ABC0000,RD Alpha,Manajer Test,Saham,Konvensional,Konvensional,IDR,1234.56,2026-06-26
BHD02D0000DEF0000,RD Beta,Manajer Test,Saham,Konvensional,Konvensional,IDR,2345.67,2026-06-26
BHD02D0000ABC0000,RD Alpha,Manajer Test,Saham,Konvensional,Konvensional,IDR,9999.99,2026-06-26
BHD02D0000GHI0000,,Manajer Test,Saham,Konvensional,Konvensional,IDR,1000,2026-06-26
CSV;

        $file = UploadedFile::fake()->createWithContent('reksa-dana.csv', $csv);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $file);

        $this->assertSame(2, $import->imported, 'imported harus menghitung record unik, bukan baris');
        $this->assertSame(2, $import->created);
        $this->assertSame(0, $import->updated);
        $this->assertSame(1, $import->duplicates);
        $this->assertSame(1, $import->skipped);

        $this->assertCount(2, ReksaDana::all());

        $alpha = ReksaDana::where('nama_reksa_dana', 'RD Alpha')->first();
        $this->assertNotNull($alpha);
        // Baris duplikat terakhir yang harus diterapkan
        $this->assertEqualsWithDelta(9999.99, (float) $alpha->nab_per_unit, 0.01);
    }

    public function test_import_existing_record_is_counted_as_updated(): void
    {
        InvestmentManager::create([
            'name' => 'Manajer Test',
            'kode_mi' => 'BHD02',
        ]);

        ReksaDana::create([
            'kode_reksa_dana' => 'BHD02D0000ABC0000',
            'nama_reksa_dana' => 'RD Alpha',
            'nama_manajer_investasi' => 'Manajer Test',
            'jenis' => 'Saham',
            'kategori' => ['Konvensional'],
            'mata_uang' => 'IDR',
        ]);

        $csv = <<<CSV
kode_reksa_dana,nama_reksa_dana,nama_manajer_investasi,jenis,kategori,kategori_produk,mata_uang,nab_per_unit,tanggal_nab
BHD02D0000ABC0000,RD Alpha,Manajer Test,Saham,Konvensional,Konvensional,IDR,1500,2026-06-26
BHD02D0000DEF0000,RD Beta,Manajer Test,Saham,Konvensional,Konvensional,IDR,2500,2026-06-26
CSV;

        $file = UploadedFile::fake()->createWithContent('reksa-dana.csv', $csv);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $file);

        $this->assertSame(2, $import->imported);
        $this->assertSame(1, $import->created);
        $this->assertSame(1, $import->updated);
        $this->assertSame(0, $import->duplicates);
        $this->assertSame(0, $import->skipped);
    }

    public function test_import_without_kode_generates_ksei_code_from_name(): void
    {
        InvestmentManager::create([
            'name' => 'Allianz',
            'kode_mi' => 'DR002',
        ]);

        $csv = <<<CSV
kode_reksa_dana,nama_reksa_dana,nama_manajer_investasi,jenis,kategori,kategori_produk,mata_uang,nab_per_unit,tanggal_nab
,Allianz Alpha Sector Rotation Kelas A,Allianz,Saham,Konvensional,Konvensional,IDR,1500,2026-06-26
CSV;

        $file = UploadedFile::fake()->createWithContent('reksa-dana.csv', $csv);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $file);

        $this->assertSame(1, $import->imported);
        $this->assertSame(1, $import->created);

        $rd = ReksaDana::where('nama_reksa_dana', 'Allianz Alpha Sector Rotation Kelas A')->first();
        $this->assertNotNull($rd);
        $this->assertSame('DR002D000AASRA000', $rd->kode_reksa_dana);
        $this->assertSame('Kelas A', $rd->kelas);
    }

    public function test_import_with_valid_kode_overrides_fields_from_code(): void
    {
        InvestmentManager::create([
            'name' => 'Manajer Test',
            'kode_mi' => 'BHD02',
        ]);

        // Kode BHD02D0000ABC0000 => jenis Saham (D), kategori Konvensional, kelas 000, IDR
        // Excel sengaja diisi salah agar terbukti di-override oleh kode
        $csv = <<<CSV
kode_reksa_dana,nama_reksa_dana,nama_manajer_investasi,jenis,kategori,kategori_produk,mata_uang,nab_per_unit,tanggal_nab
BHD02D0000ABC0000,RD Alpha,Manajer Test,Campuran,Syariah,Syariah,USD,1500,2026-06-26
CSV;

        $file = UploadedFile::fake()->createWithContent('reksa-dana.csv', $csv);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $file);

        $rd = ReksaDana::where('kode_reksa_dana', 'BHD02D0000ABC0000')->first();
        $this->assertNotNull($rd);
        $this->assertSame('Saham', $rd->jenis);
        $this->assertSame('Konvensional', $rd->kategori_produk);
        $this->assertSame(['Konvensional'], $rd->kategori);
        $this->assertSame('IDR', $rd->mata_uang);
    }

    public function test_import_existing_record_fills_kelas_from_valid_kode(): void
    {
        InvestmentManager::create([
            'name' => 'Manajer Test',
            'kode_mi' => 'BHD02',
        ]);

        // Existing dengan kelas kosong, kode valid dengan kelas A00
        ReksaDana::create([
            'kode_reksa_dana' => 'BHD02D0000ABCA000',
            'nama_reksa_dana' => 'RD Alpha',
            'nama_manajer_investasi' => 'Manajer Test',
            'jenis' => 'Saham',
            'kategori' => ['Konvensional'],
            'mata_uang' => 'IDR',
            // kelas sengaja tidak diisi
        ]);

        $csv = <<<CSV
kode_reksa_dana,nama_reksa_dana,nama_manajer_investasi,jenis,kategori,kategori_produk,mata_uang,nab_per_unit,tanggal_nab
BHD02D0000ABCA000,RD Alpha,Manajer Test,Saham,Konvensional,Konvensional,IDR,1500,2026-06-26
CSV;

        $file = UploadedFile::fake()->createWithContent('reksa-dana.csv', $csv);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $file);

        $rd = ReksaDana::where('kode_reksa_dana', 'BHD02D0000ABCA000')->first();
        $this->assertNotNull($rd);
        $this->assertSame('Kelas A', $rd->kelas);
    }
}
