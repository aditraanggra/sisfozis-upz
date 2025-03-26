<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapZisResource\Pages;
use App\Filament\Resources\RekapZisResource\RelationManagers;
use App\Models\RekapZis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use OpenSpout\Writer\AutoFilter;

class RekapZisResource extends Resource
{
    protected static ?string $model = RekapZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Rekapitulasi';

    protected static ?string $label = 'Rekap ZIS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->required()
                    ->numeric(),
                /*  Forms\Components\TextInput::make('period')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('period_date')
                    ->required(), */
                Forms\Components\TextInput::make('total_zf_rice')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_zf_amount')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_zf_muzakki')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_zm_amount')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_zm_muzakki')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_ifs_amount')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_ifs_munfiq')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
                    ->label('Unit')
                    ->sortable(),
                /*  Tables\Columns\TextColumn::make('period')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('period_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(), */
                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('Zakat Fitrah Beras')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Zakat Fitrah Beras')),
                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('Zakat Fitrah Uang')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Zakat Fitrah Uang')),
                Tables\Columns\TextColumn::make('total_zf_muzakki')
                    ->label('Muzakki Zakat Fitrah')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Muzakki Zakat Fitrah')),
                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Zakat Mal')),
                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki Zakat Mal')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Muzakki Zakat Mal')),
                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak Sedekah')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Infak Sedekah')),
                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Munfiq')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->label('Periode')
                    ->default('tahunan')
                    ->options([
                        'harian' => 'Harian',
                        'bulanan' => 'Bulanan',
                        'tahunan' => 'Tahunan',
                    ])
            ])
            ->defaultSort('district.name', 'asc')
            ->groups([
                Tables\Grouping\Group::make('district.name')
                    ->label('Kecamatan')
                    ->collapsible(),
                Tables\Grouping\Group::make('village.name')
                    ->label('Desa')
                    ->collapsible()
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('period', 'tahunan')
            ->whereYear('period_date', 2025)
            ->with(['district', 'unit']);
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
            'index' => Pages\ListRekapZis::route('/'),
            'create' => Pages\CreateRekapZis::route('/create'),
            'edit' => Pages\EditRekapZis::route('/{record}/edit'),
        ];
    }
}
