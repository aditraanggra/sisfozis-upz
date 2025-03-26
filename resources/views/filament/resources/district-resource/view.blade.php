<div class="p-2">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4">Unit</th>
                    <th scope="col" class="px-6 py-4">Total ZF Beras</th>
                    <th scope="col" class="px-6 py-4">Total ZF Uang</th>
                    <th scope="col" class="px-6 py-4">Muzakki ZF</th>
                    <th scope="col" class="px-6 py-4">Total ZM</th>
                    <th scope="col" class="px-6 py-4">Muzakki ZM</th>
                    <th scope="col" class="px-6 py-4">Total IFS</th>
                    <th scope="col" class="px-6 py-4">Munfiq IFS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapZis as $rekap)
                <tr class="bg-white border-b">
                    <td class="px-6 py-4">{{ $rekap->unit->unit_name }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_zf_rice, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_zf_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_zf_muzakki) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_zm_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_zm_muzakki) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_ifs_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekap->total_ifs_munfiq) }}</td>
                </tr>
                @endforeach
                @if($rekapZis->isEmpty())
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center">Tidak ada data rekap ZIS</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-100">
                    <td class="px-6 py-4">Total</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_zf_rice'), 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_zf_amount'), 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_zf_muzakki')) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_zm_amount'), 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_zm_muzakki')) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_ifs_amount'), 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($rekapZis->sum('total_ifs_munfiq')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>