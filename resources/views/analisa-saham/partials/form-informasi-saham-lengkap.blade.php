{{-- Informasi Saham (Display Only for Lengkap Tab) --}}
<div class="bg-white rounded-xl border border-line overflow-hidden">
    <div class="px-5 py-4 border-b border-line">
        <h4 class="font-semibold text-primary">Informasi Saham</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-gray-50 text-muted text-xs">
                <tr>
                    <th class="px-3 py-2 text-left border-b border-line w-1/2">Field</th>
                    <th class="px-3 py-2 text-right border-b border-line w-1/2">Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-xs">
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Nama Saham</td>
                    <td class="px-3 py-1.5 text-right" x-text="nama_saham || '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Jumlah Lembar Saham</td>
                    <td class="px-3 py-1.5 text-right" x-text="jumlah_lembar_saham ? parseFloat(jumlah_lembar_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Harga</td>
                    <td class="px-3 py-1.5 text-right" x-text="harga_saham ? parseFloat(harga_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q1</td>
                    <td class="px-3 py-1.5 text-right" x-text="q1_saham ? parseFloat(q1_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q2</td>
                    <td class="px-3 py-1.5 text-right" x-text="q2_saham ? parseFloat(q2_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q3</td>
                    <td class="px-3 py-1.5 text-right" x-text="q3_saham ? parseFloat(q3_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q4</td>
                    <td class="px-3 py-1.5 text-right" x-text="q4_saham ? parseFloat(q4_saham).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Kapitalisasi Pasar</td>
                    <td class="px-3 py-1.5 text-right" x-text="kapitalisasi_pasar ? parseFloat(kapitalisasi_pasar).toLocaleString('id-ID') : '-'"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
