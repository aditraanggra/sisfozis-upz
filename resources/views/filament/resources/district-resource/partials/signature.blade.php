@php
    // Fetch the UPZ Kecamatan unit profile
    $unitKecamatan = $record->unitzis()->whereHas('category', function($q) {
        $q->where('name', 'Kecamatan');
    })->first();

    $ketua = $unitKecamatan?->unit_leader ?? '';
    $ketua = $ketua === '-' ? '..............................' : $ketua;
    
    $sekretaris = $unitKecamatan?->unit_assistant ?? '';
    $sekretaris = $sekretaris === '-' ? '..............................' : $sekretaris;
    
    $bendahara = $unitKecamatan?->unit_finance ?? '';
    $bendahara = $bendahara === '-' ? '..............................' : $bendahara;
@endphp

<style>
    .signature-container {
        width: 100%;
        margin-top: 10px;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .signature-container > tbody > tr > td {
        border: none;
        vertical-align: top;
        padding: 0;
    }
    .signature-grid {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }
    .signature-grid th, .signature-grid td {
        border: 1px solid #000;
        padding: 3px;
        vertical-align: middle;
    }
</style>

<table class="signature-container">
    <tr>
        <td style="width: 35%; padding-right: 15px;">
            <div class="mengetahui" style="margin-top: 0;">Mengetahui :</div>
            <div class="mengetahui-detail" style="font-weight: bold; margin-bottom: 40px;">Camat {{ strtoupper($record->name) }}</div>
            
            <div style="font-weight: bold; text-decoration: underline;">........................................</div>
            <div>NIP. ........................................</div>
        </td>
        <td style="width: 65%;">
            <table class="signature-grid">
                <tr>
                    <td style="width: 20%;"><span class="italic">tanggal</span></td>
                    <td style="width: 26%;">Dibuat oleh</td>
                    <td style="width: 26%;">Diperiksa oleh</td>
                    <td style="width: 28%;">Disahkan oleh</td>
                </tr>
                <tr>
                    <td rowspan="3">{{ now()->format('d-M-y') }}</td>
                    <td style="height: 45px;"></td>
                    <td style="height: 45px;"></td>
                    <td style="height: 45px;"></td>
                </tr>
                <tr>
                    <td style="font-style: italic;">{{ strtoupper($bendahara) }}</td>
                    <td style="font-style: italic;">{{ strtoupper($sekretaris) }}</td>
                    <td style="font-style: italic;">{{ strtoupper($ketua) }}</td>
                </tr>
                <tr>
                    <td style="font-style: italic;">Bendahara</td>
                    <td style="font-style: italic;">Sekretaris</td>
                    <td style="font-style: italic;">Ketua</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
