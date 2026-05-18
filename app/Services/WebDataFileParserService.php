<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parse file unduhan dari situs eksternal (CSV/XLS) ke struktur form analisa.
 * Mendukung format template analisa (sheet Sektor, Efek, Kinerja, Obligasi, Bank)
 * dan sheet tunggal dengan kolom fleksibel.
 */
class WebDataFileParserService
{
    public function parse(string $path): array
    {
        $data = [
            'nama_reksa_dana' => null,
            'jenis_reksa_dana' => null,
            'total_aum' => null,
            'total_marcap_10_efek' => null,
            'sektor' => [],
            'efek' => [],
            'kinerja' => [],
            'obligasi' => [],
            'bank' => [],
        ];

        $spreadsheet = IOFactory::load($path);

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $title = strtolower(trim($sheet->getTitle()));
            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) < 2) {
                continue;
            }

            $headers = $this->normalizeHeaders(array_shift($rows));

            match (true) {
                str_contains($title, 'sektor') => $data['sektor'] = array_merge($data['sektor'], $this->parseSektor($headers, $rows)),
                str_contains($title, 'efek') => $data['efek'] = array_merge($data['efek'], $this->parseEfek($headers, $rows)),
                str_contains($title, 'kinerja') => $data['kinerja'] = array_merge($data['kinerja'], $this->parseKinerja($headers, $rows)),
                str_contains($title, 'obligasi') => $data['obligasi'] = array_merge($data['obligasi'], $this->parseObligasi($headers, $rows)),
                str_contains($title, 'bank') => $data['bank'] = array_merge($data['bank'], $this->parseBank($headers, $rows)),
                default => $this->parseGenericSheet($data, $headers, $rows),
            };
        }

        return $data;
    }

    protected function normalizeHeaders(array $row): array
    {
        $headers = [];
        foreach ($row as $col => $val) {
            $key = strtolower(trim(preg_replace('/\s+/', '_', (string) $val)));
            $headers[$col] = $key;
        }

        return $headers;
    }

    protected function rowAssoc(array $headers, array $row): array
    {
        $out = [];
        foreach ($headers as $col => $key) {
            if ($key !== '') {
                $out[$key] = $row[$col] ?? null;
            }
        }

        return $out;
    }

    protected function parseSektor(array $headers, array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);
            $nama = $r['nama_sektor'] ?? $r['sektor'] ?? null;
            if (!$nama) {
                continue;
            }
            $items[] = ['nama_sektor' => (string) $nama, 'bobot' => $r['bobot'] ?? $r['persentase'] ?? ''];
        }

        return $items;
    }

    protected function parseEfek(array $headers, array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);
            $kode = $r['kode_efek'] ?? $r['kode'] ?? null;
            $nama = $r['nama_efek'] ?? $r['nama'] ?? null;
            if (!$kode && !$nama) {
                continue;
            }
            $items[] = [
                'kode_efek' => (string) ($kode ?? ''),
                'nama_efek' => (string) ($nama ?? ''),
                'sektor' => (string) ($r['sektor'] ?? ''),
                'bobot' => $r['bobot'] ?? $r['persentase'] ?? '',
                'kontribusi_kinerja' => $r['kontribusi_kinerja'] ?? '',
                'market_cap' => $r['market_cap'] ?? $r['marcap'] ?? '',
                'top_10' => in_array(strtolower((string) ($r['top_10'] ?? '')), ['1', 'ya', 'yes', 'true'], true),
            ];
        }

        return $items;
    }

    protected function parseKinerja(array $headers, array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);
            $periode = $r['periode'] ?? $r['bulan'] ?? $r['tanggal'] ?? null;
            $ret = $r['return_pct'] ?? $r['return'] ?? $r['nav'] ?? null;
            if (!$periode && $ret === null) {
                continue;
            }
            $items[] = ['periode' => (string) ($periode ?? ''), 'return_pct' => $ret ?? ''];
        }

        return $items;
    }

    protected function parseObligasi(array $headers, array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);
            if (empty($r['kode_obligasi']) && empty($r['nama_obligasi'])) {
                continue;
            }
            $items[] = [
                'kode_obligasi' => (string) ($r['kode_obligasi'] ?? ''),
                'nama_obligasi' => (string) ($r['nama_obligasi'] ?? ''),
                'bobot' => $r['bobot'] ?? '',
                'durasi' => $r['durasi'] ?? '',
                'rating' => (string) ($r['rating'] ?? ''),
            ];
        }

        return $items;
    }

    protected function parseBank(array $headers, array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);
            if (empty($r['nama_bank'])) {
                continue;
            }
            $items[] = [
                'nama_bank' => (string) $r['nama_bank'],
                'bobot' => $r['bobot'] ?? '',
                'car' => $r['car'] ?? '',
                'npl' => $r['npl'] ?? '',
                'klasifikasi_risiko' => (string) ($r['klasifikasi_risiko'] ?? ''),
            ];
        }

        return $items;
    }

    protected function parseGenericSheet(array &$data, array $headers, array $rows): void
    {
        foreach ($rows as $row) {
            $r = $this->rowAssoc($headers, $row);

            if (!empty($r['nama_reksa_dana'])) {
                $data['nama_reksa_dana'] = $data['nama_reksa_dana'] ?? (string) $r['nama_reksa_dana'];
            }
            if (!empty($r['total_aum'])) {
                $data['total_aum'] = $data['total_aum'] ?? $r['total_aum'];
            }

            if (!empty($r['nama_sektor']) || !empty($r['sektor'])) {
                $data['sektor'][] = [
                    'nama_sektor' => (string) ($r['nama_sektor'] ?? $r['sektor']),
                    'bobot' => $r['bobot'] ?? '',
                ];
            }

            if (!empty($r['kode_efek']) || !empty($r['nama_efek'])) {
                $data['efek'][] = [
                    'kode_efek' => (string) ($r['kode_efek'] ?? ''),
                    'nama_efek' => (string) ($r['nama_efek'] ?? ''),
                    'sektor' => (string) ($r['sektor'] ?? ''),
                    'bobot' => $r['bobot'] ?? '',
                    'kontribusi_kinerja' => '',
                    'market_cap' => '',
                    'top_10' => false,
                ];
            }

            $periode = $r['tanggal'] ?? $r['periode'] ?? null;
            $nav = $r['nav'] ?? $r['nab'] ?? $r['nab_per_unit'] ?? null;
            if ($periode && $nav !== null && $nav !== '') {
                $data['kinerja'][] = [
                    'periode' => (string) $periode,
                    'return_pct' => $nav,
                ];
            }
        }
    }
}
