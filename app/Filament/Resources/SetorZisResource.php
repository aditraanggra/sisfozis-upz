<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorZisResource\Pages;
use App\Filament\Resources\SetorZisResource\RelationManagers;
use App\Models\District;
use App\Models\SetorZis;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SetorZisResource extends Resource
{
    protected static ?string $model = SetorZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->label('ID UPZ')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->label('Tanggal Transaksi')
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('zf_amount_deposit')
                    ->label('Setor Zakat Fitrah (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zf_rice_deposit')
                    ->label('Setor Zakat Fitrah (Beras)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zm_amount_deposit')
                    ->label('Setor Zakat Mal (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ifs_amount_deposit')
                    ->label('Setor Infaq Sedekah (Uang)')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_deposit')
                    ->label('Total Setor')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('validation')
                    ->label('Validasi'),
                Forms\Components\Placeholder::make('current_upload')
                    ->label('Gambar Bukti Setor Saat Ini')
                    ->content(function ($record) {
                        if (!$record || !$record->upload) {
                            return 'Belum ada gambar';
                        }
                        $url = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($record->upload);
                        return new \Illuminate\Support\HtmlString(
                            '<img src="' . e($url) . '" style="max-width: 400px; max-height: 300px; border-radius: 8px; object-fit: contain;" />'
                        );
                    })
                    ->visible(fn ($record) => $record !== null),
                Forms\Components\FileUpload::make('upload')
                    ->label('Upload Bukti Setor Baru')
                    ->disk('cloudinary')
                    ->directory('bukti-setor')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->openable()
                    ->downloadable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction('view')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Nama UPZ')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trx_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zf_amount_deposit')
                    ->label('Setor Zakat Fitrah (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Fitrah (Uang)')),
                Tables\Columns\TextColumn::make('zf_rice_deposit')
                    ->label('Setor Zakat Fitrah (Beras)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Fitrah (Beras)')),
                Tables\Columns\TextColumn::make('zm_amount_deposit')
                    ->label('Setor Zakat Mal (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Mal')),
                Tables\Columns\TextColumn::make('ifs_amount_deposit')
                    ->label('Setor Infaq Sedekah (Uang)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor Infak')),
                Tables\Columns\TextColumn::make('total_deposit')
                    ->label('Total Setor')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Setor')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('validation')
                    ->label('Validasi')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('upload')
                    ->label('Bukti Setor')
                    ->disk('cloudinary')
                    ->searchable(),
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
                SelectFilter::make('trx_year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $years[$year] = (string) $year;
                        }
                        return $years;
                    })
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['value'], fn(Builder $q, $year) => $q->whereYear('trx_date', $year))
                    ),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn() => SetorZis::distinct()->pluck('status', 'status')->filter()),
                SelectFilter::make('validation')
                    ->label('Validasi')
                    ->options(fn() => SetorZis::distinct()->pluck('validation', 'validation')->filter()),
                SelectFilter::make('district')
                    ->label('Kecamatan')
                    ->options(fn() => District::pluck('name', 'id'))
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'],
                            fn(Builder $q, $districtId) =>
                            $q->whereHas('unit', fn($q) => $q->where('district_id', $districtId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin()),
                SelectFilter::make('village')
                    ->label('Desa')
                    ->options(function () {
                        $user = User::current();
                        if ($user && $user->isUpzKecamatan() && $user->district_id) {
                            return Village::where('district_id', $user->district_id)->pluck('name', 'id');
                        }
                        return Village::pluck('name', 'id');
                    })
                    ->searchable()
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'],
                            fn(Builder $q, $villageId) =>
                            $q->whereHas('unit', fn($q) => $q->where('village_id', $villageId))
                        )
                    )
                    ->visible(fn() => User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsUpzKecamatan()),
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
                    ->visible(fn() => !User::currentIsUpzDesa()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\TextEntry::make('unit.unit_name')
                            ->label('Nama UPZ'),
                        Infolists\Components\TextEntry::make('trx_date')
                            ->label('Tanggal Transaksi')
                            ->date(),
                        Infolists\Components\TextEntry::make('zf_amount_deposit')
                            ->label('Setor Zakat Fitrah (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('zf_rice_deposit')
                            ->label('Setor Zakat Fitrah (Beras)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('zm_amount_deposit')
                            ->label('Setor Zakat Mal (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('ifs_amount_deposit')
                            ->label('Setor Infaq Sedekah (Uang)')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('total_deposit')
                            ->label('Total Setor')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('validation')
                            ->label('Validasi'),
                        Infolists\Components\ImageEntry::make('upload')
                            ->label('Bukti Setor')
                            ->disk('cloudinary')
                            ->width(400)
                            ->height(300),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetorZis::route('/'),
            'create' => Pages\CreateSetorZis::route('/create'),
            'edit' => Pages\EditSetorZis::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
