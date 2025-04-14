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
        DAFTAR REKAPITULASI PENERIMAAN ZIS RAMADHAN TAHUN 1446 H/2025 M <br>
        BERBASIS SISFOZIS
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Desa {{ $record->name }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Unit Pengumpul Zakat (UPZ)</th>
                <th colspan="5">Zakat Fitrah</th>
                <th rowspan="3">Zakat Mal</th>
                <th rowspan="3">Infak Sedekah</th>
                <th rowspan="3">Total ZIS</th>
            </tr>
            <tr>
                <th>Beras (Kg)</th>
                <th>Harga Beras (Rp)</th>
                <th>Beras Diuangkan (Rp)</th>
                <th>Uang (Rp)</th>
                <th>Total Zakat Fitrah (Rp)</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3=1x2</th>
                <th>4</th>
                <th>5=3+4</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($rekapZis as $rekap)
            @php
            $total_zf_rice_value = ($rekap->unit->rice_price) * ($rekap->total_zf_rice);
            $total_zf = ($rekap->total_zf_amount) + $total_zf_rice_value;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $rekap->unit->unit_name }}</td>
                <td>{{ number_format($rekap->total_zf_rice, 2) }}</td>
                <td>{{ number_format($rekap->unit->rice_price, 2) }}</td>
                <td>{{ number_format($total_zf_rice_value, 2) }}</td>
                <td>{{ number_format($rekap->total_zf_amount, 2) }}</td>
                <td>{{ number_format($total_zf, 2) }}</td>
                <td>{{ number_format($rekap->total_zm_amount, 2) }}</td>
                <td>{{ number_format($rekap->total_ifs_amount, 2) }}</td>
                <td>{{ number_format($total_zf + $rekap->total_zm_amount + $rekap->total_ifs_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @if($rekapZis->isNotEmpty())
        <tfoot>
            <tr class="bold">
                <td colspan="2">Total Penerimaan</td>
                <td>{{ number_format($rekapZis->sum('total_zf_rice'), 2) }}</td>
                <td></td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return $rekap->total_zf_rice * $rekap->unit->rice_price;
        }), 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zf_amount'), 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return $rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price);
        }), 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zm_amount'), 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_ifs_amount'), 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return ($rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price)) + 
                   $rekap->total_zm_amount + $rekap->total_ifs_amount;
        }), 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">Total Setor (30%)</td>
                <td>{{ number_format($rekapZis->sum('total_zf_rice')*0.3, 2) }}</td>
                <td></td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return $rekap->total_zf_rice * $rekap->unit->rice_price;
        })*0.3, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zf_amount')*0.3, 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return $rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price);
        })*0.3, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_zm_amount')*0.3, 2) }}</td>
                <td>{{ number_format($rekapZis->sum('total_ifs_amount')*0.3, 2) }}</td>
                <td>{{ number_format($rekapZis->sum(function($rekap) {
            return ($rekap->total_zf_amount + ($rekap->total_zf_rice * $rekap->unit->rice_price)) + 
                   $rekap->total_zm_amount + $rekap->total_ifs_amount;
        })*0.3, 2) }}</td>
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