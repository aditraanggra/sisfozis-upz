<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    // ...
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $title = 'Pengumpulan';

    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('year')
                            ->label('Year')
                            ->options([
                                date('Y') => date('Y'),
                                date('Y', strtotime('-1 year')) => date('Y', strtotime('-1 year')),
                                date('Y', strtotime('-2 year')) => date('Y', strtotime('-2 year')),
                            ])
                            ->default(date('Y'))
                            ->placeholder('Select Year')
                            ->searchable(),
                        DatePicker::make('startDate')
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                        // ...
                    ])
                    ->columns(3),

            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\TotalZisOverview::class,
            \App\Filament\Widgets\AllZisOverview::class,
            \App\Filament\Widgets\MuzakiChart::class,
            \App\Filament\Widgets\ZisChart::class,
        ];
    }
}
