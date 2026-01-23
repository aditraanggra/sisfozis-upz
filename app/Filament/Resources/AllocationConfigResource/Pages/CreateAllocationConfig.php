<?php

namespace App\Filament\Resources\AllocationConfigResource\Pages;

use App\Filament\Resources\AllocationConfigResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAllocationConfig extends CreateRecord
{
    protected static string $resource = AllocationConfigResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validate setor + kelola = 100
        $setor = (float) ($data['setor_percentage'] ?? 0);
        $kelola = (float) ($data['kelola_percentage'] ?? 0);
        $sum = bcadd((string) $setor, (string) $kelola, 2);

        if (bccomp($sum, '100', 2) !== 0) {
            Notification::make()
                ->title('Validasi Gagal')
                ->body('Persentase Setor dan Kelola harus berjumlah 100%. Saat ini: '.$sum.'%')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Konfigurasi alokasi berhasil dibuat';
    }
}
