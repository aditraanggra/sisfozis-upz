<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZfPaymentTypeResource\Pages;
use App\Models\User;
use App\Models\ZfPaymentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ZfPaymentTypeResource extends Resource
{
    protected static ?string $model = ZfPaymentType::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 7;

    protected static ?string $label = 'Tipe Pembayaran ZF';

    protected static ?string $pluralLabel = 'Tipe Pembayaran ZF';

    public static function canAccess(): bool
    {
        return User::currentIsSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'beras' => 'Beras',
                        'uang' => 'Uang',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('rice_amount')
                    ->label('Jumlah Beras (Kg)')
                    ->numeric()
                    ->step(0.01)
                    ->visible(fn($get) => $get('type') === 'beras'),
                Forms\Components\TextInput::make('money_amount')
                    ->label('Jumlah Uang (Rp)')
                    ->numeric()
                    ->visible(fn($get) => $get('type') === 'uang'),
                Forms\Components\TextInput::make('sk_reference')
                    ->label('Referensi SK')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'beras' => 'success',
                        'uang' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('rice_amount')
                    ->label('Jumlah Beras')
                    ->suffix(' Kg')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('money_amount')
                    ->label('Jumlah Uang')
                    ->money('IDR')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sk_reference')
                    ->label('Referensi SK')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'beras' => 'Beras',
                        'uang' => 'Uang',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZfPaymentTypes::route('/'),
            'create' => Pages\CreateZfPaymentType::route('/create'),
            'edit' => Pages\EditZfPaymentType::route('/{record}/edit'),
        ];
    }
}
