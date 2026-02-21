<?php

namespace App\Http\Controllers;

use App\Services\AlokasiReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for the allocation report endpoint.
 *
 * Returns updated allocation data (setor, pendistribusian, hak amil)
 * for all ZIS types in a single JSON response.
 */
class AlokasiReportController extends Controller
{
    protected AlokasiReportService $reportService;

    public function __construct(AlokasiReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate allocation report for a specific unit.
     *
     * Required query parameters:
     *   - unit_id: int (the UPZ unit ID)
     *
     * Optional query parameters:
     *   - year: int (fiscal year, defaults to current year)
     */
    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:unit_zis,id',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        $unitId = (int) $validated['unit_id'];
        $year = (int) ($validated['year'] ?? now()->year);

        $reportData = $this->reportService->generateReport($unitId, $year);

        return response()->json([
            'data' => $reportData,
        ]);
    }
}
