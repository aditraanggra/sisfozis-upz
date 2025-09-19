<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Zakat Fitrah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        tfoot {
            font-weight: bold;
        }

        tfoot td {
            background-color: #f0f0f0;
        }

        tfoot tr {
            font-weight: bold;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .unit-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .signature td {
            border: none;
            text-align: center;
            padding-top: 40px;
        }

        .italic {
            font-style: italic;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        DAFTAR REKAPITULASI PENERIMAAN ZIS UPZ MASJID/RT/RW TAHUN 1447 H/2025 M <br>
        BERBASIS SISFOZIS
    </div>

    <div class="unit-title">Kecamatan {{$record->name}}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">No Register</th>
                <th rowspan="2">Unit Pengumpul Zakat (UPZ)</th>
                <th rowspan="2">Desa</th>
                <th colspan="2">Zakat Fitrah</th>
                <th colspan="2">Zakat Mal</th>
                <th colspan="2">Infak Sedekah</th>
                <th rowspan="2">Total ZIS</th>
            </tr>
            <tr>
                <th>Jumlah (Rp)</th>
                <th>Muzakki</th>
                <th>Jumlah (Rp)</th>
                <th>Muzakki</th>
                <th>Jumlah (Rp)</th>
                <th>Munfiq</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($rekapZis->sortBy('unit.village_id') as $rekap)
            @php
            $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
            $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
            $total_zm = $rekap->total_zm_amount;
            $total_ifs = $rekap->total_ifs_amount;
            $total_muzakki_zf = $rekap->total_zf_muzakki;
            $total_muzakki_zm = $rekap->total_zm_muzakki;
            $total_munfiq_ifs = $rekap->total_ifs_munfiq;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $rekap->unit->no_register }}</td>
                <td>{{ $rekap->unit->unit_name }}</td>
                <td>{{ $rekap->unit->village->name }}</td>
                <td>{{ number_format($total_zf*0.7, 2) }}</td>
                <td>{{ number_format($rekap->total_zf_muzakki) }}</td>
                <td>{{ number_format($total_zm*0.7, 2) }}</td>
                <td>{{ number_format($rekap->total_zm_muzakki) }}</td>
                <td>{{ number_format($total_ifs*0.7, 2) }}</td>
                <td>{{ number_format($rekap->total_ifs_munfiq) }}</td>
                <td>{{ number_format(($total_zf + $total_zm + $total_ifs)*0.7, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @if($rekapZis->isNotEmpty())
        <tfoot>
            <tr class="bold">
                <td colspan="2">Total Penerimaan</td>
                <td></td>
                <td></td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return $rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price);
        })*0.7, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zf_muzakki')) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zm_amount')*0.7, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zm_muzakki')) }}</td>
                <td>{{ number_format($rekapZis->sum('total_ifs_amount')*0.7, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_ifs_munfiq')) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return ($rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price)) + 
                   $rekap->total_zm_amount + $rekap->total_ifs_amount;
        })*0.7, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>

</html>