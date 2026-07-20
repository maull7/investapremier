{{-- Informasi Obligasi (Display Only for Lengkap Tab) --}}
<div class="bg-white rounded-xl border border-line overflow-hidden">
    <div class="px-5 py-4 border-b border-line">
        <h4 class="font-semibold text-primary">Informasi Obligasi</h4>
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
                    <td class="px-3 py-1.5">Nama Obligasi</td>
                    <td class="px-3 py-1.5 text-right" x-text="info_nama_obligasi || '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">YTM (%)</td>
                    <td class="px-3 py-1.5 text-right" x-text="info_ytm ? parseFloat(info_ytm).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Harga</td>
                    <td class="px-3 py-1.5 text-right" x-text="harga_obligasi ? parseFloat(harga_obligasi).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q1</td>
                    <td class="px-3 py-1.5 text-right" x-text="q1_obligasi ? parseFloat(q1_obligasi).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q2</td>
                    <td class="px-3 py-1.5 text-right" x-text="q2_obligasi ? parseFloat(q2_obligasi).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q3</td>
                    <td class="px-3 py-1.5 text-right" x-text="q3_obligasi ? parseFloat(q3_obligasi).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Q4</td>
                    <td class="px-3 py-1.5 text-right" x-text="q4_obligasi ? parseFloat(q4_obligasi).toLocaleString('id-ID') : '-'"></td>
                </tr>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-1.5">Nominal Penerbitan</td>
                    <td class="px-3 py-1.5 text-right" x-text="info_nominal_penerbitan ? parseFloat(info_nominal_penerbitan).toLocaleString('id-ID') : '-'"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
