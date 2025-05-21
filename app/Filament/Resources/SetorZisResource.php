<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorZisResource\Pages;
use App\Filament\Resources\SetorZisResource\RelationManagers;
use App\Models\SetorZis;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SetorZisResource extends Resource
{
    protected static ?string $model = SetorZis::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Rekap & Transaksi';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_id')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('trx_date')
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('zf_amount_deposit')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zf_rice_deposit')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('zm_amount_deposit')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ifs_amount_deposit')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_deposit')
                    ->readOnly()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('validation')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('upload')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.unit_name')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('validation')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('upload')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
