<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllocationConfigResource\Pages;
use App\Models\AllocationConfig;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AllocationConfigResource extends Resource
{
    protected static ?string $model = AllocationConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Konfigurasi Alokasi';

    protected static ?string $modelLabel = 'Konfigurasi Alokasi';

    protected static ?string $pluralModelLabel = 'Konfigurasi Alokasi';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('zis_type')
                ->label('Jenis ZIS')
                ->options(AllocationConfig::TYPES)
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('effective_year')
                ->label('Tahun Berlaku')
                ->numeric()
                ->required()
                ->minValue(2020)
                ->maxValue(2100)
                ->default(now()->year),

            Forms\Components\TextInput::make('setor_percentage')
                ->label('Persentase Setor (%)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->default(30.00)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state !== null && is_numeric($state)) {
                        $kelola = bcsub('100', $state, 2);
                        $set('kelola_percentage', $kelola);
                    }
                })
                ->helperText('Persentase dana yang disetor ke BAZNAS'),

            Forms\Components\TextInput::make('kelola_percentage')
                ->label('Persentase Kelola (%)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->default(70.00)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state !== null && is_numeric($state)) {
                        $setor = bcsub('100', $state, 2);
                        $set('setor_percentage', $setor);
                    }
                })
                ->helperText('Persentase dana yang dikelola lokal'),

            Forms\Components\TextInput::make('amil_percentage')
                ->label('Persentase Amil dari Kelola (%)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->default(12.50)
                ->helperText('Persentase hak amil dihitung dari dana kelola'),

            Forms\Components\Textarea::make('description')
                ->label('Keterangan')
                ->rows(2)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('zis_type')
                    ->label('Jenis ZIS')
                    ->formatStateUsing(fn($state) => AllocationConfig::TYPES[$state] ?? $state)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('effective_year')
                    ->label('Tahun Berlaku')
                    ->sortable(),

                Tables\Columns\TextColumn::make('setor_percentage')
                    ->label('Setor (%)')
                    ->suffix('%')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('kelola_percentage')
                    ->label('Kelola (%)')
                    ->suffix('%')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('amil_percentage')
                    ->label('Amil (%)')
                    ->suffix('%')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('effective_year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('zis_type')
                    ->label('Jenis ZIS')
                    ->options(AllocationConfig::TYPES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAllocationConfigs::route('/'),
            'create' => Pages\CreateAllocationConfig::route('/create'),
            'edit' => Pages\EditAllocationConfig::route('/{record}/edit'),
        ];
    }

    /**
     * Restrict access to super_admin, admin, and tim_sisfo roles
     */
    public static function canViewAny(): bool
    {
        return User::currentIsSuperAdmin() || User::currentIsAdmin() || User::currentIsTimSisfo();
    }
}
