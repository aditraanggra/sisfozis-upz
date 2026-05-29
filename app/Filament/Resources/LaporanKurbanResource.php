<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanKurbanResource\Pages;
use App\Models\District;
use App\Models\LaporanKurban;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanKurbanResource extends Resource
{
    protected static ?string $model = LaporanKurban::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Kurban';

    protected static ?string $navigationLabel = 'Laporan Kurban';

    protected static ?string $modelLabel = 'Laporan Kurban';

    protected static ?string $pluralModelLabel = 'Laporan Kurban';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction('view')
            ->persistFiltersInSession()
            ->columns([
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.district.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tahun_hijriah')
                    ->label('Tahun Hijriah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_masehi')
                    ->label('Tahun Masehi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jml_sapi')
                    ->label('Sapi')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Sapi')),
                Tables\Columns\TextColumn::make('jml_kerbau')
                    ->label('Kerbau')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Kerbau')),
                Tables\Columns\TextColumn::make('jml_kambing')
                    ->label('Kambing')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Kambing')),
                Tables\Columns\TextColumn::make('jml_domba')
                    ->label('Domba')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Domba')),
                Tables\Columns\TextColumn::make('total_ekor')
                    ->label('Total Ekor')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Hewan')),
                Tables\Columns\TextColumn::make('total_shohibul')
                    ->label('Shohibul')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Mudhohi')),
                Tables\Columns\TextColumn::make('jml_paket_daging')
                    ->label('Paket Daging')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Paket Daging')),
                Tables\Columns\TextColumn::make('jml_penerima')
                    ->label('Penerima')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Penerima')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Tanggal Submit')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_masehi')
                    ->label('Tahun Masehi')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear + 1; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }

                        return $years;
                    }),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn () => LaporanKurban::distinct()->pluck('status', 'status')->filter()),
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
                SelectFilter::make('unit_id')
                    ->label('Unit UPZ')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return UnitZis::where('district_id', $user->district_id)->pluck('unit_name', 'id');
                        }

                        return UnitZis::pluck('unit_name', 'id');
                    })
                    ->searchable()
                    ->visible(fn () => ! User::currentIsUpzDesa()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\TextEntry::make('unit.unit_name')
                            ->label('Nama UPZ'),
                        Infolists\Components\TextEntry::make('unit.district.name')
                            ->label('Kecamatan'),
                        Infolists\Components\TextEntry::make('unit.village.name')
                            ->label('Desa'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Pelapor'),
                        Infolists\Components\TextEntry::make('tahun_hijriah')
                            ->label('Tahun Hijriah'),
                        Infolists\Components\TextEntry::make('tahun_masehi')
                            ->label('Tahun Masehi'),
                        Infolists\Components\Section::make('Data Hewan Kurban')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('jml_sapi')->label('Sapi'),
                                        Infolists\Components\TextEntry::make('jml_kerbau')->label('Kerbau'),
                                        Infolists\Components\TextEntry::make('jml_kambing')->label('Kambing'),
                                        Infolists\Components\TextEntry::make('jml_domba')->label('Domba'),
                                    ]),
                                Infolists\Components\TextEntry::make('total_ekor')->label('Total Ekor'),
                            ]),
                        Infolists\Components\Section::make('Data Shohibul & Distribusi')
                            ->schema([
                                Infolists\Components\TextEntry::make('total_shohibul')->label('Total Shohibul'),
                                Infolists\Components\IconEntry::make('shohibul_dikoreksi')
                                    ->label('Shohibul Dikoreksi')
                                    ->boolean(),
                                Infolists\Components\TextEntry::make('alasan_koreksi')
                                    ->label('Alasan Koreksi')
                                    ->visible(fn ($record) => $record && $record->shohibul_dikoreksi),
                                Infolists\Components\TextEntry::make('jml_paket_daging')->label('Jumlah Paket Daging'),
                                Infolists\Components\TextEntry::make('jml_penerima')->label('Jumlah Penerima'),
                                Infolists\Components\TextEntry::make('kelompok_penerima')
                                    ->label('Kelompok Penerima')
                                    ->badge()
                                    ->separator(','),
                                Infolists\Components\TextEntry::make('catatan')->label('Catatan'),
                            ]),
                        Infolists\Components\Section::make('Status & Verifikasi')
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'submitted' => 'info',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('no_referensi')->label('No Referensi'),
                                Infolists\Components\TextEntry::make('submitted_at')
                                    ->label('Tanggal Submit')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('verifier.name')->label('Verifikasi Oleh'),
                                Infolists\Components\TextEntry::make('verified_at')
                                    ->label('Tanggal Verifikasi')
                                    ->dateTime(),
                            ]),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(\App\Filament\Exports\LaporanKurbanExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada laporan kurban')
            ->emptyStateDescription('Data laporan kurban akan ditampilkan di sini.');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['unit.district', 'unit.village', 'user', 'verifier']);

        $user = User::current();

        if ($user?->isUpzKecamatan() && $user->district_id) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('district_id', $user->district_id);
            });
        } elseif ($user?->isUpzDesa() && $user->village_id) {
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
            'index' => Pages\ListLaporanKurbans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
