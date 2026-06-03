<?php

namespace Database\Seeders;

use App\Models\RatingObligasi;
use Illuminate\Database\Seeder;

class RatingObligasiSeeder extends Seeder
{
    private array $ratings = [
        ['kode' => 'AAA',  'nama' => 'AAA (Triple A)',               'keterangan' => 'Peringkat tertinggi. Kemampuan sangat kuat untuk memenuhi kewajiban keuangan jangka panjang.',     'urutan' => 1],
        ['kode' => 'AA+',  'nama' => 'AA+ (Double A Plus)',          'keterangan' => 'Peringkat sangat tinggi, sedikit lebih rendah dari AAA.',                                         'urutan' => 2],
        ['kode' => 'AA',   'nama' => 'AA (Double A)',                'keterangan' => 'Peringkat sangat tinggi. Kemampuan sangat kuat untuk memenuhi kewajiban keuangan.',                'urutan' => 3],
        ['kode' => 'AA-',  'nama' => 'AA- (Double A Minus)',         'keterangan' => 'Peringkat sangat tinggi, sedikit lebih rendah dari AA.',                                          'urutan' => 4],
        ['kode' => 'A+',   'nama' => 'A+ (Single A Plus)',           'keterangan' => 'Peringkat tinggi, risiko kredit rendah.',                                                         'urutan' => 5],
        ['kode' => 'A',    'nama' => 'A (Single A)',                 'keterangan' => 'Peringkat tinggi, namun sedikit lebih rentan terhadap perubahan kondisi ekonomi.',                 'urutan' => 6],
        ['kode' => 'A-',   'nama' => 'A- (Single A Minus)',          'keterangan' => 'Peringkat tinggi, risiko kredit cukup rendah.',                                                  'urutan' => 7],
        ['kode' => 'BBB+', 'nama' => 'BBB+ (Triple B Plus)',         'keterangan' => 'Peringkat investment grade terendah, kemampuan memenuhi kewajiban keuangan memadai.',              'urutan' => 8],
        ['kode' => 'BBB',  'nama' => 'BBB (Triple B)',               'keterangan' => 'Peringkat investment grade, kemampuan memenuhi kewajiban keuangan memadai.',                       'urutan' => 9],
        ['kode' => 'BBB-', 'nama' => 'BBB- (Triple B Minus)',        'keterangan' => 'Peringkat investment grade terendah, lebih rentan terhadap perubahan ekonomi.',                    'urutan' => 10],
        ['kode' => 'BB+',  'nama' => 'BB+ (Double B Plus)',          'keterangan' => 'Peringkat spekulatif, risiko kredit lebih tinggi.',                                              'urutan' => 11],
        ['kode' => 'BB',   'nama' => 'BB (Double B)',                'keterangan' => 'Peringkat spekulatif, risiko kredit tinggi.',                                                    'urutan' => 12],
        ['kode' => 'BB-',  'nama' => 'BB- (Double B Minus)',         'keterangan' => 'Peringkat spekulatif, risiko kredit cukup tinggi.',                                              'urutan' => 13],
        ['kode' => 'B+',   'nama' => 'B+ (Single B Plus)',           'keterangan' => 'Peringkat spekulatif tinggi, risiko kredit sangat tinggi.',                                       'urutan' => 14],
        ['kode' => 'B',    'nama' => 'B (Single B)',                 'keterangan' => 'Peringkat spekulatif tinggi, kemampuan memenuhi kewajiban sangat rentan.',                         'urutan' => 15],
        ['kode' => 'B-',   'nama' => 'B- (Single B Minus)',          'keterangan' => 'Peringkat spekulatif sangat tinggi, risiko kredit sangat tinggi.',                                'urutan' => 16],
        ['kode' => 'CCC',  'nama' => 'CCC (Triple C)',               'keterangan' => 'Peringkat default potensial, risiko kredit sangat tinggi.',                                       'urutan' => 17],
        ['kode' => 'D',    'nama' => 'D (Default)',                  'keterangan' => 'Telah mengalami default atau gagal bayar.',                                                      'urutan' => 18],
    ];

    public function run(): void
    {
        foreach ($this->ratings as $rating) {
            RatingObligasi::updateOrCreate(
                ['kode' => $rating['kode']],
                $rating
            );
        }
    }
}
