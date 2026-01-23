<?php

namespace App\View\Composers;

use App\Services\AllocationConfigService;
use Illuminate\View\View;

class AllocationComposer
{
    public function __construct(
        protected AllocationConfigService $allocationConfigService
    ) {}

    /**
     * Bind allocation data to the view.
     *
     * Extracts date from view data (checking multiple common date field names)
     * and provides allocation percentages for all ZIS types.
     */
    public function compose(View $view): void
    {
        // Extract date from view data, falling back to current date
        // This ensures reports always have allocation data available
        $viewData = $view->getData();
        $date = $viewData['date']
            ?? $viewData['period_date']
            ?? $viewData['transaction_date']
            ?? $viewData['report_date']
            ?? now();

        $view->with('allocations', [
            'zf' => $this->allocationConfigService->getAllocation('zf', $date),
            'zm' => $this->allocationConfigService->getAllocation('zm', $date),
            'ifs' => $this->allocationConfigService->getAllocation('ifs', $date),
        ]);
    }
}
