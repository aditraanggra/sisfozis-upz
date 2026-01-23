<?php

namespace App\Observers;

use App\Models\AllocationConfig;
use App\Services\AllocationConfigService;

class AllocationConfigObserver
{
    public function __construct(
        protected AllocationConfigService $allocationConfigService
    ) {}

    /**
     * Handle the AllocationConfig "saved" event.
     * This covers both created and updated events.
     */
    public function saved(AllocationConfig $config): void
    {
        $this->allocationConfigService->clearCache();
    }

    /**
     * Handle the AllocationConfig "deleted" event.
     */
    public function deleted(AllocationConfig $config): void
    {
        $this->allocationConfigService->clearCache();
    }
}
