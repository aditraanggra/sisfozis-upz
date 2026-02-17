<?php

namespace App\Http\Controllers;

use App\Services\ZisReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for the consolidated ZIS report endpoint.
 *
 * Returns all ZIS report data in a single JSON response,
 * suitable for frontend display and PDF generation.
 */
class ZisReportController extends Controller
{
    protected ZisReportService $reportService;

    public function __construct(ZisReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate consolidated ZIS report for a specific unit.
     *
     * Required query parameters:
     *   - unit_id: int (the UPZ unit ID)
     *
     * Optional query parameters:
     *   - periode: string (harian|bulanan|tahunan)
     *   - from_date: date (Y-m-d, start of date range)
     *   - to_date: date (Y-m-d, end of date range)
     */
    public function report(Request $request): JsonResponse
    {
        // Validate incoming parameters
        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:unit_zis,id',
            'periode' => 'nullable|string|in:harian,bulanan,tahunan',
            'year' => 'nullable|integer|min:2020|max:2100',
            'from_date' => 'nullable|date|date_format:Y-m-d',
            'to_date' => 'nullable|date|date_format:Y-m-d|required_with:from_date|after_or_equal:from_date',
        ]);

        $unitId = (int) $validated['unit_id'];
        $filters = array_filter([
            'periode' => $validated['periode'] ?? null,
            'year' => $validated['year'] ?? null,
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ]);

        $reportData = $this->reportService->generateReport($unitId, $filters);

        return response()->json([
            'data' => $reportData,
        ]);
    }
}
