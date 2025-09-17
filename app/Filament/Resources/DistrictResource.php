<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use App\Filament\Exports\DistrictExporter;
use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExportAction; // Ensure this is the correct namespace for ExportAction
use Filament\Tables\Actions\ExportAction as ActionsExportAction;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Unit Pengumpul Zakat (UPZ)';

    protected static ?int $navigationSort = 7;

    protected static ?string $label = 'Rekap ZIS Per Kecamatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No')
                    ->rowIndex()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ZIS')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_amount') +
                            $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_amount') +
                            $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_rice')
                    ->label('Zakat Fitrah (Beras)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_rice');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_amount')
                    ->label('Zakat Fitrah (Uang)')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_amount')
                    ->label('Zakat Mal')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_amount')
                    ->label('Infak Sedekah')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_amount');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zf_muzakki')
                    ->label('Muzakki ZF')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zf_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_zm_muzakki')
                    ->label('Muzakki ZM')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_zm_muzakki');
                    })
                    ->numeric(),

                Tables\Columns\TextColumn::make('total_ifs_munfiq')
                    ->label('Munfiq')
                    ->getStateUsing(function ($record) {
                        return $record->rekapZis
                            ->where('period', 'tahunan')
                            ->where('period_date', '2025-01-01')
                            ->sum('total_ifs_munfiq');
                    })
                    ->numeric(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([

                    Tables\Actions\Action::make('pdf')
                        ->label('Report DKM/RT/RW')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(Model $record) => route('report.pdf', $record))
                        ->openUrlInNewTab(),

                ])
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->size('lg')

            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //
            ])
            ->headerActions([
                ActionsExportAction::make()
                    ->exporter(DistrictExporter::class)
            ])
            //->defaultSort('total', 'desc')
            ->recordUrl(null);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (User::currentIsUpzKecamatan() && $user->district_id) {
            $query->where('district_id', $user->district_id);
        }

        return $query
            ->withSum('rekapZis', 'total_zf_rice')
            ->withSum('rekapZis', 'total_zf_amount')
            ->withSum('rekapZis', 'total_zf_muzakki')
            ->withSum('rekapZis', 'total_zm_amount')
            ->withSum('rekapZis', 'total_zm_muzakki')
            ->withSum('rekapZis', 'total_ifs_amount')
            ->withSum('rekapZis', 'total_ifs_munfiq')
            ->with('rekapZis');
    }

    // Tambahkan method view untuk modal detail
    public function view(District $record)
    {
        return view('filament.resources.district-resource.pages.view-district', [
            'record' => $record,
            'rekapZis' => $record->rekapZis
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
            'index' => Pages\ListDistricts::route('/'),
            'create' => Pages\CreateDistrict::route('/create'),
            'edit' => Pages\EditDistrict::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
