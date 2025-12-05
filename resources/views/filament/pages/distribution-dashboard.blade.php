<x-filament-panels::page>
    {{ $this->filtersForm }}

    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="$this->getColumns()"
    />
</x-filament-panels::page>

