<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapAlokasiSetorResource\Pages;
use App\Models\District;
use App\Models\RekapAlokasi;
use App\Models\SetorZis;
use App\Models\User;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapAlokasiSetorResource extends Resource
{
    protected static ?string $model = RekapAlokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?string $navigationLabel = 'Rekap Alokasi Setor';

    protected static ?string $modelLabel = 'Rekap Alokasi Setor';

    protected static ?string $pluralModelLabel = 'Rekap Alokasi Setor';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->persistFiltersInSession()
            ->columns([
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('unit.district.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_date')
                    ->label('Tahun')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('Y') : '-')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                // ────────────────────────────────────────────────
                //  Kolom alokasi total (dari rekap_alokasi)
                // ────────────────────────────────────────────────
                Tables\Columns\TextColumn::make('total_setor_zf_amount')
                    ->label('Alokasi ZF (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_setor_zf_rice')
                    ->label('Alokasi ZF (Beras kg)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_setor_zm')
                    ->label('Alokasi ZM (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_setor_ifs')
                    ->label('Alokasi IFS (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // ────────────────────────────────────────────────
                //  Kolom sudah disetor (dari rekap_setor)
                // ────────────────────────────────────────────────
                Tables\Columns\TextColumn::make('sudah_setor_zf_amount')
                    ->label('Sudah Setor ZF (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sudah_setor_zf_rice')
                    ->label('Sudah Setor ZF (Beras kg)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sudah_setor_zm')
                    ->label('Sudah Setor ZM (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sudah_setor_ifs')
                    ->label('Sudah Setor IFS (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // ────────────────────────────────────────────────
                //  Kolom sisa yang harus disetor (kolom utama)
                // ────────────────────────────────────────────────
                Tables\Columns\TextColumn::make('sisa_zf_amount')
                    ->label('Sisa ZF (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->color(fn ($state) => ((int) $state) > 0 ? 'warning' : 'success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Sisa ZF Uang')),

                Tables\Columns\TextColumn::make('sisa_zf_rice')
                    ->label('Sisa ZF (Beras kg)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->color(fn ($state) => ((float) $state) > 0 ? 'warning' : 'success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Sisa ZF Beras')),

                Tables\Columns\TextColumn::make('sisa_zm')
                    ->label('Sisa ZM (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->color(fn ($state) => ((int) $state) > 0 ? 'warning' : 'success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Sisa ZM')),

                Tables\Columns\TextColumn::make('sisa_ifs')
                    ->label('Sisa IFS (Uang)')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->sortable()
                    ->color(fn ($state) => ((int) $state) > 0 ? 'warning' : 'success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Sisa IFS')),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }

                        return $years;
                    })
                    ->default(now()->year)
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $year) => $q->whereYear('rekap_alokasi.periode_date', $year)
                        )
                    ),

                SelectFilter::make('district')
                    ->label('Kecamatan')
                    ->options(fn () => District::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $districtId) => $q->whereHas('unit', fn ($q) => $q->where('district_id', $districtId))
                        )
                    )
                    ->visible(fn () => User::currentIsSuperAdmin() || User::currentIsAdmin()),

                SelectFilter::make('village')
                    ->label('Desa')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return Village::where('district_id', $user->district_id)->orderBy('name')->pluck('name', 'id');
                        }

                        return Village::orderBy('name')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $villageId) => $q->whereHas('unit', fn ($q) => $q->where('village_id', $villageId))
                        )
                    )
                    ->visible(fn () => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan()),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkAction::make('proses_setor_zis')
                    ->label('Proses Setor ZIS')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Setor ZIS dari Alokasi')
                    ->modalDescription(fn (Collection $records) => 'Anda akan membuat '.$records->count().' entri Setor ZIS '
                        .'berdasarkan sisa alokasi setor masing-masing unit.'
                    )
                    ->deselectRecordsAfterCompletion()
                    ->form([
                        Forms\Components\DatePicker::make('trx_date')
                            ->label('Tanggal Setor')
                            ->required()
                            ->default(today())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('status')
                            ->label('Jenis Setor')
                            ->options([
                                'Tunai' => 'Tunai',
                                'Non Tunai' => 'Non Tunai',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('deposit_destination')
                            ->label('Tujuan Setoran')
                            ->options([
                                'upz_desa' => 'UPZ Desa',
                                'upz_kecamatan' => 'UPZ Kecamatan',
                                'baznas' => 'BAZNAS',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('validation')
                            ->label('Status Validasi')
                            ->options([
                                'Belum Verifikasi' => 'Belum Verifikasi',
                                'Terverifikasi' => 'Terverifikasi',
                                'Perlu Konfirmasi' => 'Perlu Konfirmasi',
                            ])
                            ->default('Belum Verifikasi')
                            ->required()
                            ->native(false),

                        Forms\Components\FileUpload::make('upload')
                            ->label('Upload Bukti Setor')
                            ->required()
                            ->disk('cloudinary')
                            ->directory('sisfo/bukti_setor')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Upload dokumen bukti transfer/setor (1 bukti untuk semua unit yang dipilih)')
                            ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string {
                                $user = \Illuminate\Support\Facades\Auth::user();
                                $unit = $user ? $user->unitZis : null;
                                $noRegister = $unit ? $unit->no_register : 'admin';
                                $namaUnit = $unit ? \Illuminate\Support\Str::slug($unit->unit_name) : 'admin';
                                $tanggal = date('Ymd');
                                $timestamp = time();
                                $extension = $file->getClientOriginalExtension();
                                $customName = "setor_bulk_{$tanggal}_{$noRegister}_{$namaUnit}_{$timestamp}";
                                $folder = 'sisfo/bukti_setor';

                                $cloudinary = app(\Cloudinary\Cloudinary::class);
                                $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                                    'folder' => $folder,
                                    'public_id' => $customName,
                                    'resource_type' => 'auto',
                                ]);

                                return $result['public_id'].'.'.($result['format'] ?? $extension);
                            }),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $processed = 0;
                        $skipped = 0;

                        DB::transaction(function () use ($records, $data, &$processed, &$skipped) {
                            foreach ($records as $record) {
                                // ── 1. Lock baris rekap_alokasi agar tidak ada transaksi
                                //       lain yang membaca nilai stale secara bersamaan.
                                $locked = RekapAlokasi::where('id', $record->id)
                                    ->lockForUpdate()
                                    ->first();

                                if (! $locked) {
                                    $skipped++;

                                    continue;
                                }

                                // ── 2. Hitung total yang SUDAH disetor dari rekap_setor
                                //       secara fresh dari DB (bukan dari kolom virtual di $record).
                                $tahun = (int) \Carbon\Carbon::parse($locked->periode_date)->format('Y');

                                $sudah = DB::table('rekap_setor')
                                    ->where('unit_id', $locked->unit_id)
                                    ->whereYear('periode_date', $tahun)
                                    ->selectRaw('
                                        COALESCE(SUM(t_setor_zf_amount), 0) AS zf_amount,
                                        COALESCE(SUM(t_setor_zf_rice),   0) AS zf_rice,
                                        COALESCE(SUM(t_setor_zm),        0) AS zm,
                                        COALESCE(SUM(t_setor_ifs),       0) AS ifs
                                    ')
                                    ->first();

                                // ── 3. Hitung sisa dari nilai otoritatif (locked row).
                                $sisaZfAmount = max(0, (int) $locked->total_setor_zf_amount - (int) $sudah->zf_amount);
                                $sisaZfRice = max(0.0, (float) $locked->total_setor_zf_rice - (float) $sudah->zf_rice);
                                $sisaZm = max(0, (int) $locked->total_setor_zm - (int) $sudah->zm);
                                $sisaIfs = max(0, (int) $locked->total_setor_ifs - (int) $sudah->ifs);

                                // ── 4. Lewati jika seluruh sisa sudah 0.
                                if ($sisaZfAmount === 0 && $sisaZfRice == 0.0 && $sisaZm === 0 && $sisaIfs === 0) {
                                    $skipped++;

                                    continue;
                                }

                                // ── 5. Buat record SetorZis dengan nilai sisa yang sudah di-recompute.
                                $totalDeposit = $sisaZfAmount + $sisaZm + $sisaIfs;

                                SetorZis::create([
                                    'unit_id' => $locked->unit_id,
                                    'trx_date' => $data['trx_date'],
                                    'zf_amount_deposit' => $sisaZfAmount,
                                    'zf_rice_deposit' => $sisaZfRice,
                                    'zf_rice_sold_amount' => 0,
                                    'zf_rice_sold_price' => 0,
                                    'zf_rice_sold_proof' => null,
                                    'zm_amount_deposit' => $sisaZm,
                                    'ifs_amount_deposit' => $sisaIfs,
                                    'total_deposit' => $totalDeposit,
                                    'status' => $data['status'],
                                    'validation' => $data['validation'],
                                    'deposit_destination' => $data['deposit_destination'],
                                    'upload' => $data['upload'] ?? '',
                                ]);

                                $processed++;
                            }
                        });

                        if ($processed === 0) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak ada yang diproses')
                                ->body('Semua unit yang dipilih sudah memenuhi alokasi setor (sisa = 0).')
                                ->send();

                            return;
                        }

                        $body = "{$processed} unit berhasil diproses ke Setor ZIS.";
                        if ($skipped > 0) {
                            $body .= " {$skipped} unit dilewati karena sisa alokasi sudah 0.";
                        }

                        Notification::make()
                            ->success()
                            ->title('Setor ZIS berhasil dibuat')
                            ->body($body)
                            ->send();
                    }),
            ]);
    }

    /**
     * Query utama:
     *  - Ambil rekap_alokasi dengan periode = 'tahunan'
     *  - Hitung "sisa" = alokasi − total yang sudah ada di rekap_setor (tahun yang sama)
     *  - Filter data berdasarkan role pengguna
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('rekap_alokasi.periode', 'tahunan')
            ->selectRaw('
                rekap_alokasi.*,

                /* ── Sisa ZF Uang ─────────────────────────────────── */
                GREATEST(0, rekap_alokasi.total_setor_zf_amount - COALESCE((
                    SELECT SUM(rs.t_setor_zf_amount)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0)) AS sisa_zf_amount,

                /* ── Sisa ZF Beras ─────────────────────────────────── */
                GREATEST(0, rekap_alokasi.total_setor_zf_rice - COALESCE((
                    SELECT SUM(rs.t_setor_zf_rice)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0)) AS sisa_zf_rice,

                /* ── Sisa ZM ────────────────────────────────────────── */
                GREATEST(0, rekap_alokasi.total_setor_zm - COALESCE((
                    SELECT SUM(rs.t_setor_zm)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0)) AS sisa_zm,

                /* ── Sisa IFS ───────────────────────────────────────── */
                GREATEST(0, rekap_alokasi.total_setor_ifs - COALESCE((
                    SELECT SUM(rs.t_setor_ifs)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0)) AS sisa_ifs,

                /* ── Sudah Setor ZF Uang ───────────────────────────── */
                COALESCE((
                    SELECT SUM(rs.t_setor_zf_amount)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0) AS sudah_setor_zf_amount,

                /* ── Sudah Setor ZF Beras ─────────────────────────── */
                COALESCE((
                    SELECT SUM(rs.t_setor_zf_rice)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0) AS sudah_setor_zf_rice,

                /* ── Sudah Setor ZM ───────────────────────────────── */
                COALESCE((
                    SELECT SUM(rs.t_setor_zm)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0) AS sudah_setor_zm,

                /* ── Sudah Setor IFS ──────────────────────────────── */
                COALESCE((
                    SELECT SUM(rs.t_setor_ifs)
                    FROM rekap_setor rs
                    WHERE rs.unit_id = rekap_alokasi.unit_id
                      AND EXTRACT(YEAR FROM rs.periode_date) = EXTRACT(YEAR FROM rekap_alokasi.periode_date)
                ), 0) AS sudah_setor_ifs
            ')
            ->with(['unit.district', 'unit.village']);

        $user = Auth::user();

        if (User::currentIsUpzKecamatan() && $user?->district_id) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('district_id', $user->district_id);
            });
        } elseif (User::currentIsUpzDesa() && $user?->village_id) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('village_id', $user->village_id);
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapAlokasiSetor::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
