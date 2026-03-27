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
        DAFTAR REKAPITULASI HAK OPERATOR SISFO TAHUN {{ $year }}
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Desa {{ $record->name }} | Kecamatan {{$record->district->name}}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">No Register</th>
                <th rowspan="2">Unit Pengumpul Zakat (UPZ)</th>
                <th colspan="1">Setor ZF ({{ $allocations['zf']['setor'] }}%)</th>
                <th colspan="1">Hak OP (3.5%)</th>
                <th rowspan="2">Jumlah Muzakki</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2=1*3.5%</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @php $zfSetorPct = $allocations['zf']['setor'] / 100; @endphp
            @foreach($rekapZis->sortBy('unit.category_id') as $rekap)
            @php
            $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
            $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
            $setor_zf = $total_zf * $zfSetorPct;
            $hak_op = $setor_zf * 0.035;
            $jml_baris_transaksi = $rekap->zf->count();
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $rekap->unit->no_register }}</td>
                <td>{{ $rekap->unit->unit_name }}</td>
                <td>{{ number_format($setor_zf, 2) }}</td>
                <td>{{ number_format($hak_op, 2) }}</td>
                <td>{{ $rekap->total_zf_muzakki }}</td>
            </tr>
            @endforeach
        </tbody>
        @if($rekapZis->isNotEmpty())
        @php $zfSetorPctFooter = $allocations['zf']['setor'] / 100; @endphp
        <tfoot>
            <tr class="bold">
                <td colspan="2">Total Penerimaan</td>
                <td></td>
                <td>{{ number_format($rekapZis->sum(function($rekap) use ($zfSetorPctFooter) {
                    $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
                    $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
                    return $total_zf * $zfSetorPctFooter;
                }), 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) use ($zfSetorPctFooter) {
                    $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
                    $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
                    $setor_zf = $total_zf * $zfSetorPctFooter;
                    return $setor_zf * 0.035;
                }), 2) }}</td>
                <td>{{ $rekapZis->sum('total_zf_muzakki') }}</td>
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

    <div style="margin-top: 20px; font-size: 11px;">
        <p><strong>Catatan:</strong></p>
        <p>Alokasi 1,5% dari hak operator akan digunakan untuk pelaksanaan bimbingan teknis (Bimtek) aplikasi SISFO tingkat kecamatan.</p>
    </div>
</body>

</html>