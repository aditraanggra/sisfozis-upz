<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class DistributionDashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $title = 'Pendistribusian';

    protected static ?string $navigationLabel = 'Pendistribusian';

    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 2;

    protected static string $routePath = 'distribution-dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('year')
                            ->label('Tahun')
                            ->options([
                                date('Y') => date('Y'),
                                date('Y', strtotime('-1 year')) => date('Y', strtotime('-1 year')),
                                date('Y', strtotime('-2 year')) => date('Y', strtotime('-2 year')),
                            ])
                            ->default(date('Y'))
                            ->placeholder('Pilih Tahun')
                            ->searchable()
                            ->live(),
                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now())
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir')
                            ->minDate(fn(Get $get) => $get('startDate'))
                            ->maxDate(now())
                            ->live(),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DistributionStatsWidget::class,
            \App\Filament\Widgets\DistributionByAsnafWidget::class,
            \App\Filament\Widgets\DistributionByProgramWidget::class,
        ];
    }
}
