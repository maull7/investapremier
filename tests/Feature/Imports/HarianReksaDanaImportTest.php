<?php

namespace Tests\Feature\Imports;

use App\Imports\HarianReksaDanaImport;
use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class HarianReksaDanaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_counts_unique_records_and_detects_duplicates(): void
    {
        ReksaDana::create([
            'nama_reksa_dana' => 'RD Alpha',
            'nama_manajer_investasi' => 'Manajer Test',
            'jenis' => 'Saham',
            'kategori' => ['Konvensional'],
            'mata_uang' => 'IDR',
        ]);

        ReksaDana::create([
            'nama_reksa_dana' => 'RD Beta',
            'nama_manajer_investasi' => 'Manajer Test',
            'jenis' => 'Saham',
            'kategori' => ['Konvensional'],
            'mata_uang' => 'IDR',
        ]);

        $csv = <<<CSV
nama_reksa_dana,tanggal,nab_per_unit,total_dana_kelolaan,unit_penyertaan
RD Alpha,2026-06-26,1000,1000000,1000
RD Beta,2026-06-26,2000,2000000,2000
RD Alpha,2026-06-26,1500,1500000,1500
RD Gamma,2026-06-26,3000,3000000,3000
,2026-06-26,500,,
CSV;

        $file = UploadedFile::fake()->createWithContent('harian.csv', $csv);

        $import = new HarianReksaDanaImport();
        Excel::import($import, $file);

        $this->assertSame(3, $import->imported, 'imported harus menghitung kombinasi RD+tanggal unik');
        $this->assertSame(3, $import->created);
        $this->assertSame(0, $import->updated);
        $this->assertSame(1, $import->duplicates);
        $this->assertSame(1, $import->skipped);

        $this->assertCount(3, HargaReksaDana::all());

        $alpha = HargaReksaDana::whereHas('reksaDana', fn ($q) => $q->where('nama_reksa_dana', 'RD Alpha'))
            ->where('tanggal', '2026-06-26')
            ->first();
        $this->assertNotNull($alpha);
        // Baris duplikat terakhir yang harus diterapkan
        $this->assertEqualsWithDelta(1500, (float) $alpha->nab_per_unit, 0.01);
    }

    public function test_import_existing_daily_price_is_counted_as_updated(): void
    {
        $rd = ReksaDana::create([
            'nama_reksa_dana' => 'RD Alpha',
            'nama_manajer_investasi' => 'Manajer Test',
            'jenis' => 'Saham',
            'kategori' => ['Konvensional'],
            'mata_uang' => 'IDR',
        ]);

        HargaReksaDana::create([
            'reksa_dana_id' => $rd->id,
            'tanggal' => '2026-06-26',
            'nab_per_unit' => 1000,
        ]);

        $csv = <<<CSV
nama_reksa_dana,tanggal,nab_per_unit,total_dana_kelolaan,unit_penyertaan
RD Alpha,2026-06-26,2000,2000000,2000
CSV;

        $file = UploadedFile::fake()->createWithContent('harian.csv', $csv);

        $import = new HarianReksaDanaImport();
        Excel::import($import, $file);

        $this->assertSame(1, $import->imported);
        $this->assertSame(0, $import->created);
        $this->assertSame(1, $import->updated);
        $this->assertSame(0, $import->duplicates);
        $this->assertSame(0, $import->skipped);
    }
}
