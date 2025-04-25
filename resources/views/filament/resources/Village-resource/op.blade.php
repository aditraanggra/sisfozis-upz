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
        DAFTAR REKAPITULASI HAK OPERATOR SISFO TAHUN 1446 H/2025 M
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Desa {{ $record->name }} | Kecamatan {{$record->district->name}}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">No Register</th>
                <th rowspan="2">Unit Pengumpul Zakat (UPZ)</th>
                <th colspan="1">Setor ZF (30%)</th>
                <th colspan="1">Hak OP (5%)</th>
                <th rowspan="2">Total Transaksi ZF</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2=1*5%</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($rekapZis->sortBy('unit.category_id') as $rekap)
            @php
            $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
            $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
            $setor_zf = $total_zf * 0.3;
            $hak_op = $setor_zf * 0.05;
            $jml_baris_transaksi = $rekap->zf->count();
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $rekap->unit->no_register }}</td>
                <td>{{ $rekap->unit->unit_name }}</td>
                <td>{{ number_format($setor_zf, 2) }}</td>
                <td>{{ number_format($hak_op, 2) }}</td>
                <td>{{ $jml_baris_transaksi }}</td>
            </tr>
            @endforeach
        </tbody>
        @if($rekapZis->isNotEmpty())
        <tfoot>
            <tr class="bold">
                <td colspan="2">Total Penerimaan</td>
                <td></td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
                    $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
                    $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
                    return $total_zf * 0.3;
                }), 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
                    $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
                    $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
                    $setor_zf = $total_zf * 0.3;
                    return $setor_zf * 0.05;
                }), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <table class="signature" width="100%">
        <tr>
            <td><span class="italic">tanggal</span></td>
            <td>Dibuat oleh</td>
            <td>Diperiksa oleh</td>
            <td>Disahkan oleh</td>
        </tr>
        <tr>
            <td></td>
            <td height="8"></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td><span class="italic">Bendahara</span></td>
            <td><span class="italic">Sekretaris</span></td>
            <td><span class="italic">Ketua</span></td>
        </tr>
    </table>
</body>

</html>