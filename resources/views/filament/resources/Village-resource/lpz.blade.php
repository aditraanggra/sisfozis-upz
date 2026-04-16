<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengelolaan ZIS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .w-full { width: 100%; }
        
        .header-title {
            font-size: 14px;
            text-align: center;
            font-weight: bold;
        }
        
        .header-container {
            position: relative;
            margin-bottom: 20px;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
        }
        .baznas-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
        }
        .box-info {
            border: 1px solid black;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .box-info table {
            width: 100%;
        }
        .box-info td {
            padding: 2px;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid black;
            padding: 5px;
        }
        table.data-table th {
            background-color: #f0f0f0;
            text-align: center;
        }
        
        table.data-table td.text-right {
            text-align: right;
        }
        
        .signature-table {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }
        .signature-table td {
            width: 25%;
            vertical-align: top;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* Page 2 specific */
        .title-h2 {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        
        .bukti-setor {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .bukti-setor img {
            max-width: 100%;
            max-height: 400px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/Logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    @foreach($units as $index => $unit)
        @php
            $rekapZisEnabled = $rekapZis->get($unit->id);
            $rekapPendisEnabled = $rekapPendis->get($unit->id);
            $rekapSetorEnabled = $rekapSetor->get($unit->id);
            $setorZisList = $setorZisGrouped->get($unit->id, collect());
            
            // Zakat Fitrah
            $zf_rice = $rekapZisEnabled?->total_zf_rice ?? 0;
            $zf_amount = $rekapZisEnabled?->total_zf_amount ?? 0;
            $zf_pendis_rice = $rekapPendisEnabled?->t_pendis_zf_rice ?? 0;
            $zf_pendis_amount = $rekapPendisEnabled?->t_pendis_zf_amount ?? 0;
            $zf_muzakki = $rekapZisEnabled?->total_zf_muzakki ?? 0;
            $zf_pm = $rekapPendisEnabled?->t_pm ?? 0;
            $zf_setor_rice = $rekapSetorEnabled?->t_setor_zf_rice ?? 0;
            $zf_setor_amount = $rekapSetorEnabled?->t_setor_zf_amount ?? 0;
            
            // Zakat Mal
            $zm_amount = $rekapZisEnabled?->total_zm_amount ?? 0;
            $zm_pendis_amount = $rekapPendisEnabled?->t_pendis_zm ?? 0;
            $zm_muzakki = $rekapZisEnabled?->total_zm_muzakki ?? 0;
            $zm_setor_amount = $rekapSetorEnabled?->t_setor_zm ?? 0;
            
            // Infak Sedekah
            $ifs_amount = $rekapZisEnabled?->total_ifs_amount ?? 0;
            $ifs_pendis_amount = $rekapPendisEnabled?->t_pendis_ifs ?? 0;
            $ifs_munfiq = $rekapZisEnabled?->total_ifs_munfiq ?? 0;
            $ifs_setor_amount = $rekapSetorEnabled?->t_setor_ifs ?? 0;
        @endphp
        
        <!-- PAGE 1: URAIAN -->
        <div class="header-container">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="baznas-logo" alt="Logo">
            @endif
            <div class="header-title">
                BADAN AMIL ZAKAT NASIONAL (BAZNAS)<br>
                KABUPATEN CIANJUR<br>
                LAPORAN PENGELOLAAN ZIS<br>
                Tahun {{ $year }}
            </div>
        </div>
        
        <div class="box-info">
            <table>
                <tr>
                    <td style="width: 15%;">Nama UPZ</td>
                    <td style="width: 35%;">: {{ strtoupper($unit->unit_name) }}</td>
                    <td style="width: 15%;">Desa/Kelurahan</td>
                    <td style="width: 35%;">: {{ strtoupper($village->name) }}</td>
                </tr>
                <tr>
                    <td>No Register</td>
                    <td>: {{ $unit->no_register ?? '-' }}</td>
                    <td>Kecamatan</td>
                    <td>: {{ strtoupper($unit->district?->name ?? '-') }}</td>
                </tr>
            </table>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>URAIAN</th>
                    <th style="width: 35%;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <!-- Zakat Fitrah -->
                <tr>
                    <td colspan="2" class="font-bold">1. PENERIMAAN DAN PENYALURAN ZAKAT FITRAH</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Penerimaan (Uang | Beras)</td>
                    <td class="text-right">Rp {{ number_format($zf_amount, 0, ',', '.') }} | {{ number_format($zf_rice, 1, ',', '.') }} Kg</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Pendistribusian (Uang | Beras)</td>
                    <td class="text-right">Rp {{ number_format($zf_pendis_amount, 0, ',', '.') }} | {{ number_format($zf_pendis_rice, 1, ',', '.') }} Kg</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Jumlah Muzakki</td>
                    <td class="text-right">{{ number_format($zf_muzakki) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Jumlah Penerima Manfaat</td>
                    <td class="text-right">{{ number_format($zf_pm) }}</td>
                </tr>
                <tr>
                    <td class="font-bold">&nbsp;&nbsp;&nbsp; Setoran ke BAZNAS (30%)</td>
                    <td class="text-right font-bold">Rp {{ number_format($zf_setor_amount, 0, ',', '.') }} | {{ number_format($zf_setor_rice, 2, ',', '.') }} Kg</td>
                </tr>
                
                <!-- Zakat Mal -->
                <tr>
                    <td colspan="2" class="font-bold">2. PENERIMAAN DAN PENYALURAN ZAKAT MAAL</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Penerimaan</td>
                    <td class="text-right">Rp {{ number_format($zm_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Pendistribusian</td>
                    <td class="text-right">Rp {{ number_format($zm_pendis_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Jumlah Muzakki</td>
                    <td class="text-right">{{ number_format($zm_muzakki) }}</td>
                </tr>
                <tr>
                    <td class="font-bold">&nbsp;&nbsp;&nbsp; Setoran ke BAZNAS (100%)</td>
                    <td class="text-right font-bold">Rp {{ number_format($zm_setor_amount, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Infak Sedekah -->
                <tr>
                    <td colspan="2" class="font-bold">3. PENERIMAAN DAN PENYALURAN INFAK SEDEKAH</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Penerimaan</td>
                    <td class="text-right">Rp {{ number_format($ifs_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Total Pendistribusian</td>
                    <td class="text-right">Rp {{ number_format($ifs_pendis_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp; Jumlah Munfiq</td>
                    <td class="text-right">{{ number_format($ifs_munfiq) }}</td>
                </tr>
                <tr>
                    <td class="font-bold">&nbsp;&nbsp;&nbsp; Setoran ke BAZNAS (80%)</td>
                    <td class="text-right font-bold">Rp {{ number_format($ifs_setor_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        <table class="signature-table">
            <tr>
                <td>Mengetahui,</td>
                <td>Ketua UPZ</td>
                <td>Sekretaris</td>
                <td>Bendahara</td>
            </tr>
            <tr>
                <td style="padding-top: 60px;" class="font-bold text-center">Ketua DKM</td>
                <td style="padding-top: 60px;" class="font-bold text-center">
                    <div style="border-bottom: 1px solid black; display: inline-block; padding: 0 10px; min-width: 100px;">
                        {{ strtoupper($unit->unit_leader) ?: '__________________' }}
                    </div>
                </td>
                <td style="padding-top: 60px;" class="font-bold text-center">
                    <div style="border-bottom: 1px solid black; display: inline-block; padding: 0 10px; min-width: 100px;">
                        {{ strtoupper($unit->unit_assistant) ?: '__________________' }}
                    </div>
                </td>
                <td style="padding-top: 60px;" class="font-bold text-center">
                    <div style="border-bottom: 1px solid black; display: inline-block; padding: 0 10px; min-width: 100px;">
                        {{ strtoupper($unit->unit_finance) ?: '__________________' }}
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 40px; font-size: 10px; color: gray;">
            Laporan ini dicetak otomatis pada {{ date('Y-m-d H:i:s') }}
        </div>
        
        <!-- PAGE 2: REKAP SETORAN -->
        <div class="page-break"></div>
        <div class="title-h2">REKAP SETORAN UPZ</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>ZF (Uang)</th>
                    <th>ZF (Beras)</th>
                    <th>Konversi Beras (Rp)</th>
                    <th>ZAKAT MAL</th>
                    <th>INFAK SEDEKAH</th>
                    <th>KET</th>
                </tr>
            </thead>
            <tbody>
                @forelse($setorZisList as $setoran)
                <tr>
                    <td class="text-center">{{ $setoran->trx_date ? $setoran->trx_date->format('Y-m-d') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($setoran->zf_amount_deposit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($setoran->zf_rice_deposit, 1, ',', '.') }} Kg</td>
                    <td class="text-right">Rp {{ number_format($setoran->zf_rice_sold_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($setoran->zm_amount_deposit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($setoran->ifs_amount_deposit, 0, ',', '.') }}</td>
                    <td class="text-center">{{ ucfirst($setoran->deposit_destination ?? 'Tunai') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada setoran di tahun ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($setorZisList->isNotEmpty())
            <div class="mb-2" style="font-weight: bold;">Bukti Setor:</div>
            <div class="bukti-setor text-center">
            @foreach($setorZisList as $setoran)
                @if($setoran->upload)
                    @php
                        $proofUrl = \App\Filament\Resources\LpzResource::getCloudinaryImageUrl($setoran->upload);
                    @endphp
                    @if($proofUrl)
                        <img src="{{ $proofUrl }}" alt="Bukti Setor" style="display: block; margin: 0 auto 20px auto; max-width: 90%;">
                    @endif
                @endif
            @endforeach
            </div>
        @endif
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
