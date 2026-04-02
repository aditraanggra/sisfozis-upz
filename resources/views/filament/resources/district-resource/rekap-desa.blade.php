<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Penerimaan ZIS Per Desa</title>
    <style>
        @page {
            margin: 30px 40px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-size: 8.5px;
        }

        tfoot {
            font-weight: bold;
        }

        tfoot td {
            background-color: #f0f0f0;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .unit-title {
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .section-row td {
            font-weight: bold;
            text-align: left;
            background-color: #fafafa;
        }

        .signature-table {
            margin-top: 15px;
        }

        .signature-table td {
            border: none;
            text-align: center;
            padding: 5px 10px;
            vertical-align: top;
        }

        .italic {
            font-style: italic;
        }

        .bold {
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .col-number td {
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 9px;
        }

        .mengetahui {
            text-align: left;
            margin-top: 20px;
            font-weight: bold;
        }

        .mengetahui-detail {
            text-align: left;
            margin-top: 5px;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 10px;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    {{-- ============================================= --}}
    {{-- HALAMAN 1: ZAKAT FITRAH --}}
    {{-- ============================================= --}}
    @php
        $kelolaZf = $allocations['zf']['kelola'] / 100;
        $setorZf = $allocations['zf']['setor'] / 100;
        $amilZf = $allocations['zf']['amil'] / 100;
        $penyaluranZf = $allocations['zf']['penyaluran'] / 100;
    @endphp

    <div class="header">
        DAFTAR REKAPITULASI PENERIMAAN ZAKAT FITRAH RAMADHAN TAHUN {{ $year }} <br>
        BERBASIS SISFOZIS
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Kecamatan &nbsp;&nbsp;&nbsp;&nbsp; {{ strtoupper($record->name) }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 150px;">Unit Pengumpul Zakat (UPZ)</th>
                <th colspan="2">Jumlah</th>
                <th colspan="3">Penerimaan</th>
                <th colspan="2">Dana Yang dikelola UPZ<br>{{ $allocations['zf']['kelola'] }} %</th>
                <th rowspan="2">Setor ke<br>BAZNAS<br>({{ $allocations['zf']['setor'] }}%)</th>
            </tr>
            <tr>
                <th>Muzaki</th>
                <th>Beras<br>(Kg)</th>
                <th>ZF Uang<br>(Rupiah)</th>
                <th>ZF Beras<br>diuangkan<br>(Rupiah)</th>
                <th>Jumlah<br>Diuangkan<br>(100%)<br>(Rupiah)</th>
                <th>Penyaluran<br>UPZ<br>({{ $allocations['zf']['penyaluran'] }}%)</th>
                <th>Hak Amil<br>UPZ<br>({{ $allocations['zf']['amil'] }}%)</th>
            </tr>
            <tr class="col-number">
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
                <td>8</td>
                <td>9</td>
                <td>10</td>
            </tr>
        </thead>
        <tbody>
            @php
                $directTotalZfRiceValue = $directCollection['zf_rice_sold_amount'] ?? 0;
                $directTotalZf = ($directCollection['total_zf_amount'] ?? 0) + $directTotalZfRiceValue;
                $directKelolaAmount = $directTotalZf * $kelolaZf;
                $directPenyaluranAmount = $directKelolaAmount * $penyaluranZf;
                $directAmilAmount = $directKelolaAmount * $amilZf;
                $directSetorAmount = $directTotalZf * $setorZf;
            @endphp
            <tr class="section-row">
                <td>A</td>
                <td colspan="9" class="text-left">Penghimpunan Langsung</td>
            </tr>
            <tr>
                <td></td>
                <td class="text-left">UPZ KECAMATAN {{ strtoupper($record->name) }}</td>
                <td>{{ number_format($directCollection['total_zf_muzakki'] ?? 0) }}</td>
                <td>{{ number_format($directCollection['total_zf_rice'] ?? 0, 2) }}</td>
                <td>{{ number_format($directCollection['total_zf_amount'] ?? 0, 2) }}</td>
                <td>{{ number_format($directTotalZfRiceValue, 2) }}</td>
                <td>{{ number_format($directTotalZf, 2) }}</td>
                <td>{{ number_format($directPenyaluranAmount, 2) }}</td>
                <td>{{ number_format($directAmilAmount, 2) }}</td>
                <td>{{ number_format($directSetorAmount, 2) }}</td>
            </tr>
            <tr class="section-row">
                <td>B</td>
                <td colspan="9" class="text-left">Penghimpunan Via UPZ DESA</td>
            </tr>
            @php $no = 1; @endphp
            @foreach($villageSummaries as $summary)
            @php
                $totalZfRiceValue = $summary['zf_rice_sold_amount'] ?? 0;
                $totalZf = $summary['total_zf_amount'] + $totalZfRiceValue;
                $kelolaAmount = $totalZf * $kelolaZf;
                $penyaluranAmount = $kelolaAmount * $penyaluranZf;
                $amilAmount = $kelolaAmount * $amilZf;
                $setorAmount = $totalZf * $setorZf;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ strtoupper($summary['village_name']) }}</td>
                <td>{{ number_format($summary['total_zf_muzakki']) }}</td>
                <td>{{ number_format($summary['total_zf_rice'], 2) }}</td>
                <td>{{ number_format($summary['total_zf_amount'], 2) }}</td>
                <td>{{ number_format($totalZfRiceValue, 2) }}</td>
                <td>{{ number_format($totalZf, 2) }}</td>
                <td>{{ number_format($penyaluranAmount, 2) }}</td>
                <td>{{ number_format($amilAmount, 2) }}</td>
                <td>{{ number_format($setorAmount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @php
            $directMuzakkiZf = $directCollection['total_zf_muzakki'] ?? 0;
            $directRice = $directCollection['total_zf_rice'] ?? 0;
            $directZfAmount = $directCollection['total_zf_amount'] ?? 0;
            $directRiceValue = $directTotalZfRiceValue;
            $directTotalZf = $directZfAmount + $directRiceValue;
            $directKelolaZf = $directTotalZf * $kelolaZf;
            $directPenyaluranZf = $directKelolaZf * $penyaluranZf;
            $directAmilZf = $directKelolaZf * $amilZf;
            $directSetorZf = $directTotalZf * $setorZf;

            $villageMuzakkiZf = $villageSummaries->sum('total_zf_muzakki');
            $villageRice = $villageSummaries->sum('total_zf_rice');
            $villageZfAmount = $villageSummaries->sum('total_zf_amount');
            $villageRiceValue = $villageSummaries->sum('zf_rice_sold_amount');
            $villageTotalZf = $villageZfAmount + $villageRiceValue;
            $villageKelolaZf = $villageTotalZf * $kelolaZf;
            $villagePenyaluranZf = $villageKelolaZf * $penyaluranZf;
            $villageAmilZf = $villageKelolaZf * $amilZf;
            $villageSetorZf = $villageTotalZf * $setorZf;

            $grandMuzakkiZf = $directMuzakkiZf + $villageMuzakkiZf;
            $grandRice = $directRice + $villageRice;
            $grandZfAmount = $directZfAmount + $villageZfAmount;
            $grandRiceValue = $directRiceValue + $villageRiceValue;
            $grandTotalZf = $directTotalZf + $villageTotalZf;
            $grandKelolaZf = $directKelolaZf + $villageKelolaZf;
            $grandPenyaluranZf = $directPenyaluranZf + $villagePenyaluranZf;
            $grandAmilZf = $directAmilZf + $villageAmilZf;
            $grandSetorZf = $directSetorZf + $villageSetorZf;
        @endphp
        <tfoot>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan Langsung</td>
                <td>{{ number_format($directMuzakkiZf) }}</td>
                <td>{{ number_format($directRice, 2) }}</td>
                <td>{{ number_format($directZfAmount, 2) }}</td>
                <td>{{ number_format($directRiceValue, 2) }}</td>
                <td>{{ number_format($directTotalZf, 2) }}</td>
                <td>{{ number_format($directPenyaluranZf, 2) }}</td>
                <td>{{ number_format($directAmilZf, 2) }}</td>
                <td>{{ number_format($directSetorZf, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan UPZ DESA</td>
                <td>{{ number_format($villageMuzakkiZf) }}</td>
                <td>{{ number_format($villageRice, 2) }}</td>
                <td>{{ number_format($villageZfAmount, 2) }}</td>
                <td>{{ number_format($villageRiceValue, 2) }}</td>
                <td>{{ number_format($villageTotalZf, 2) }}</td>
                <td>{{ number_format($villagePenyaluranZf, 2) }}</td>
                <td>{{ number_format($villageAmilZf, 2) }}</td>
                <td>{{ number_format($villageSetorZf, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format($grandMuzakkiZf) }}</td>
                <td>{{ number_format($grandRice, 2) }}</td>
                <td>{{ number_format($grandZfAmount, 2) }}</td>
                <td>{{ number_format($grandRiceValue, 2) }}</td>
                <td>{{ number_format($grandTotalZf, 2) }}</td>
                <td>{{ number_format($grandPenyaluranZf, 2) }}</td>
                <td>{{ number_format($grandAmilZf, 2) }}</td>
                <td>{{ number_format($grandSetorZf, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('filament.resources.district-resource.partials.signature', ['record' => $record])


    {{-- ============================================= --}}
    {{-- HALAMAN 2: ZAKAT MAL --}}
    {{-- ============================================= --}}
    @php
        $kelolaZm = $allocations['zm']['kelola'] / 100;
        $setorZm = $allocations['zm']['setor'] / 100;
        $amilZm = $allocations['zm']['amil'] / 100;
        $penyaluranZm = $allocations['zm']['penyaluran'] / 100;
    @endphp

    <div class="page-break"></div>

    <div class="header">
        DAFTAR REKAPITULASI PENERIMAAN ZAKAT MAL TAHUN {{ $year }} <br>
        BERBASIS SISFOZIS
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Kecamatan &nbsp;&nbsp;&nbsp;&nbsp; {{ strtoupper($record->name) }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 180px;">Desa</th>
                <th rowspan="2">Muzaki</th>
                <th rowspan="2">Jumlah Penerimaan<br>(Rupiah)<br>(100%)</th>
                <th colspan="2">Dana Yang dikelola UPZ<br>{{ $allocations['zm']['kelola'] }} %</th>
                <th rowspan="2">Setor ke<br>BAZNAS<br>({{ $allocations['zm']['setor'] }}%)</th>
            </tr>
            <tr>
                <th>Penyaluran UPZ<br>({{ $allocations['zm']['penyaluran'] }}%)</th>
                <th>Hak Amil UPZ<br>({{ $allocations['zm']['amil'] }}%)</th>
            </tr>
            <tr class="col-number">
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
            </tr>
        </thead>
        <tbody>
            <tr class="section-row">
                <td>A</td>
                <td colspan="6" class="text-left">Penghimpunan Langsung</td>
            </tr>
            @php
                $directTotalZm = $directCollection['total_zm_amount'] ?? 0;
                $directKelolaAmountZm = $directTotalZm * $kelolaZm;
                $directPenyaluranAmountZm = $directKelolaAmountZm * $penyaluranZm;
                $directAmilAmountZm = $directKelolaAmountZm * $amilZm;
                $directSetorAmountZm = $directTotalZm * $setorZm;
            @endphp
            <tr>
                <td></td>
                <td class="text-left">UPZ KECAMATAN {{ strtoupper($record->name) }}</td>
                <td>{{ number_format($directCollection['total_zm_muzakki'] ?? 0) }}</td>
                <td>{{ number_format($directTotalZm, 2) }}</td>
                <td>{{ number_format($directPenyaluranAmountZm, 2) }}</td>
                <td>{{ number_format($directAmilAmountZm, 2) }}</td>
                <td>{{ number_format($directSetorAmountZm, 2) }}</td>
            </tr>
            <tr class="section-row">
                <td>B</td>
                <td colspan="6" class="text-left">Penghimpunan Via UPZ DESA</td>
            </tr>
            @php $no = 1; @endphp
            @foreach($villageSummaries as $summary)
            @php
                $totalZm = $summary['total_zm_amount'];
                $kelolaAmountZm = $totalZm * $kelolaZm;
                $penyaluranAmountZm = $kelolaAmountZm * $penyaluranZm;
                $amilAmountZm = $kelolaAmountZm * $amilZm;
                $setorAmountZm = $totalZm * $setorZm;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ strtoupper($summary['village_name']) }}</td>
                <td>{{ number_format($summary['total_zm_muzakki']) }}</td>
                <td>{{ number_format($totalZm, 2) }}</td>
                <td>{{ number_format($penyaluranAmountZm, 2) }}</td>
                <td>{{ number_format($amilAmountZm, 2) }}</td>
                <td>{{ number_format($setorAmountZm, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @php
            $directMuzakkiZm = $directCollection['total_zm_muzakki'] ?? 0;
            $directTotalZmAmount = $directCollection['total_zm_amount'] ?? 0;
            $directKelolaZm = $directTotalZmAmount * $kelolaZm;
            $directPenyaluranZm = $directKelolaZm * $penyaluranZm;
            $directAmilZm = $directKelolaZm * $amilZm;
            $directSetorZm = $directTotalZmAmount * $setorZm;

            $villageMuzakkiZm = $villageSummaries->sum('total_zm_muzakki');
            $villageTotalZmAmount = $villageSummaries->sum('total_zm_amount');
            $villageKelolaZm = $villageTotalZmAmount * $kelolaZm;
            $villagePenyaluranZm = $villageKelolaZm * $penyaluranZm;
            $villageAmilZm = $villageKelolaZm * $amilZm;
            $villageSetorZm = $villageTotalZmAmount * $setorZm;

            $grandMuzakkiZm = $directMuzakkiZm + $villageMuzakkiZm;
            $grandTotalZm = $directTotalZmAmount + $villageTotalZmAmount;
            $grandKelolaZm = $grandTotalZm * $kelolaZm;
            $grandPenyaluranZm = $grandKelolaZm * $penyaluranZm;
            $grandAmilZm = $grandKelolaZm * $amilZm;
            $grandSetorZm = $grandTotalZm * $setorZm;
        @endphp
        <tfoot>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan Langsung</td>
                <td>{{ number_format($directMuzakkiZm) }}</td>
                <td>{{ number_format($directTotalZmAmount, 2) }}</td>
                <td>{{ number_format($directPenyaluranZm, 2) }}</td>
                <td>{{ number_format($directAmilZm, 2) }}</td>
                <td>{{ number_format($directSetorZm, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan UPZ DESA</td>
                <td>{{ number_format($villageMuzakkiZm) }}</td>
                <td>{{ number_format($villageTotalZmAmount, 2) }}</td>
                <td>{{ number_format($villagePenyaluranZm, 2) }}</td>
                <td>{{ number_format($villageAmilZm, 2) }}</td>
                <td>{{ number_format($villageSetorZm, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format($grandMuzakkiZm) }}</td>
                <td>{{ number_format($grandTotalZm, 2) }}</td>
                <td>{{ number_format($grandPenyaluranZm, 2) }}</td>
                <td>{{ number_format($grandAmilZm, 2) }}</td>
                <td>{{ number_format($grandSetorZm, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('filament.resources.district-resource.partials.signature', ['record' => $record])


    {{-- ============================================= --}}
    {{-- HALAMAN 3: INFAK SEDEKAH --}}
    {{-- ============================================= --}}
    @php
        $kelolaIfs = $allocations['ifs']['kelola'] / 100;
        $setorIfs = $allocations['ifs']['setor'] / 100;
        $amilIfs = $allocations['ifs']['amil'] / 100;
        $penyaluranIfs = $allocations['ifs']['penyaluran'] / 100;
    @endphp

    <div class="page-break"></div>

    <div class="header">
        DAFTAR REKAPITULASI PENERIMAAN INFAK SEDEKAH TAHUN {{ $year }} <br>
        BERBASIS SISFOZIS
    </div>

    <div class="unit-title">Unit Pengumpul Zakat (UPZ) Kecamatan &nbsp;&nbsp;&nbsp;&nbsp; {{ strtoupper($record->name) }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 180px;">Desa</th>
                <th rowspan="2">Munfiq</th>
                <th rowspan="2">Jumlah Penerimaan<br>(Rupiah)<br>(100%)</th>
                <th colspan="2">Dana Yang dikelola UPZ<br>{{ $allocations['ifs']['kelola'] }} %</th>
                <th rowspan="2">Setor ke<br>BAZNAS<br>({{ $allocations['ifs']['setor'] }}%)</th>
            </tr>
            <tr>
                <th>Penyaluran UPZ<br>({{ $allocations['ifs']['penyaluran'] }}%)</th>
                <th>Hak Amil UPZ<br>({{ $allocations['ifs']['amil'] }}%)</th>
            </tr>
            <tr class="col-number">
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
            </tr>
        </thead>
        <tbody>
            <tr class="section-row">
                <td>A</td>
                <td colspan="6" class="text-left">Penghimpunan Langsung</td>
            </tr>
            @php
                $directTotalIfs = $directCollection['total_ifs_amount'] ?? 0;
                $directKelolaAmountIfs = $directTotalIfs * $kelolaIfs;
                $directPenyaluranAmountIfs = $directKelolaAmountIfs * $penyaluranIfs;
                $directAmilAmountIfs = $directKelolaAmountIfs * $amilIfs;
                $directSetorAmountIfs = $directTotalIfs * $setorIfs;
            @endphp
            <tr>
                <td></td>
                <td class="text-left">UPZ KECAMATAN {{ strtoupper($record->name) }}</td>
                <td>{{ number_format($directCollection['total_ifs_munfiq'] ?? 0) }}</td>
                <td>{{ number_format($directTotalIfs, 2) }}</td>
                <td>{{ number_format($directPenyaluranAmountIfs, 2) }}</td>
                <td>{{ number_format($directAmilAmountIfs, 2) }}</td>
                <td>{{ number_format($directSetorAmountIfs, 2) }}</td>
            </tr>
            <tr class="section-row">
                <td>B</td>
                <td colspan="6" class="text-left">Penghimpunan Via UPZ DESA</td>
            </tr>
            @php $no = 1; @endphp
            @foreach($villageSummaries as $summary)
            @php
                $totalIfs = $summary['total_ifs_amount'];
                $kelolaAmountIfs = $totalIfs * $kelolaIfs;
                $penyaluranAmountIfs = $kelolaAmountIfs * $penyaluranIfs;
                $amilAmountIfs = $kelolaAmountIfs * $amilIfs;
                $setorAmountIfs = $totalIfs * $setorIfs;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ strtoupper($summary['village_name']) }}</td>
                <td>{{ number_format($summary['total_ifs_munfiq']) }}</td>
                <td>{{ number_format($totalIfs, 2) }}</td>
                <td>{{ number_format($penyaluranAmountIfs, 2) }}</td>
                <td>{{ number_format($amilAmountIfs, 2) }}</td>
                <td>{{ number_format($setorAmountIfs, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @php
            $directMunfiq = $directCollection['total_ifs_munfiq'] ?? 0;
            $directTotalIfsAmount = $directCollection['total_ifs_amount'] ?? 0;
            $directKelolaIfs = $directTotalIfsAmount * $kelolaIfs;
            $directPenyaluranIfs = $directKelolaIfs * $penyaluranIfs;
            $directAmilIfs = $directKelolaIfs * $amilIfs;
            $directSetorIfs = $directTotalIfsAmount * $setorIfs;

            $villageMunfiq = $villageSummaries->sum('total_ifs_munfiq');
            $villageTotalIfsAmount = $villageSummaries->sum('total_ifs_amount');
            $villageKelolaIfs = $villageTotalIfsAmount * $kelolaIfs;
            $villagePenyaluranIfs = $villageKelolaIfs * $penyaluranIfs;
            $villageAmilIfs = $villageKelolaIfs * $amilIfs;
            $villageSetorIfs = $villageTotalIfsAmount * $setorIfs;

            $grandMunfiq = $villageMunfiq + $directMunfiq;
            $grandTotalIfs = $villageTotalIfsAmount + $directTotalIfsAmount;
            $grandKelolaIfs = $grandTotalIfs * $kelolaIfs;
            $grandPenyaluranIfs = $grandKelolaIfs * $penyaluranIfs;
            $grandAmilIfs = $grandKelolaIfs * $amilIfs;
            $grandSetorIfs = $grandTotalIfs * $setorIfs;
        @endphp
        <tfoot>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan Langsung</td>
                <td>{{ number_format($directMunfiq) }}</td>
                <td>{{ number_format($directTotalIfsAmount, 2) }}</td>
                <td>{{ number_format($directPenyaluranIfs, 2) }}</td>
                <td>{{ number_format($directAmilIfs, 2) }}</td>
                <td>{{ number_format($directSetorIfs, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">Jumlah Pengumpulan UPZ DESA</td>
                <td>{{ number_format($villageMunfiq) }}</td>
                <td>{{ number_format($villageTotalIfsAmount, 2) }}</td>
                <td>{{ number_format($villagePenyaluranIfs, 2) }}</td>
                <td>{{ number_format($villageAmilIfs, 2) }}</td>
                <td>{{ number_format($villageSetorIfs, 2) }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format($grandMunfiq) }}</td>
                <td>{{ number_format($grandTotalIfs, 2) }}</td>
                <td>{{ number_format($grandPenyaluranIfs, 2) }}</td>
                <td>{{ number_format($grandAmilIfs, 2) }}</td>
                <td>{{ number_format($grandSetorIfs, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('filament.resources.district-resource.partials.signature', ['record' => $record])

</body>

</html>
