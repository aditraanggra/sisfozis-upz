<?php

namespace App\Http\Controllers;

use App\Http\Resources\RekapPendisResource;
use App\Models\RekapPendis;
use Illuminate\Http\Request;

class RekapPendisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = RekapPendis::with('unit');

        // Filter by unit_id if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by periode if provided
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Filter by year if provided
        if ($request->has('year')) {
            $query->whereYear('periode_date', $request->year);
        }

        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('periode_date', [$request->from_date, $request->to_date]);
        }

        // Sort options
        $sortField = $request->input('sort_by', 'periode_date');
        $sortOrder = $request->input('sort_order', 'desc');

        $rekapPendis = $query->orderBy($sortField, $sortOrder)
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => RekapPendisResource::collection($rekapPendis),
            'meta' => [
                'total' => $rekapPendis->total(),
                'per_page' => $rekapPendis->perPage(),
                'current_page' => $rekapPendis->currentPage(),
                'total_pages' => $rekapPendis->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\RekapPendisResource
     */
    public function show(RekapPendis $rekapPendis)
    {
        return new RekapPendisResource($rekapPendis->load('unit'));
    }

    /**
     * Get summary statistics for dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $query = RekapPendis::query();

        // Filter by periode if provided
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('periode_date', [$request->from_date, $request->to_date]);
        }

        // Filter by unit if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $summary = [
            'total_pendis' => [
                'zf_amount' => $query->sum('t_pendis_zf_amount'),
                'zf_rice' => $query->sum('t_pendis_zf_rice'),
                'zm' => $query->sum('t_pendis_zm'),
                'ifs' => $query->sum('t_pendis_ifs'),
            ],
            'asnaf' => [
                'fakir' => [
                    'amount' => $query->sum('t_pendis_fakir_amount'),
                    'rice' => $query->sum('t_pendis_fakir_rice'),
                ],
                'miskin' => [
                    'amount' => $query->sum('t_pendis_miskin_amount'),
                    'rice' => $query->sum('t_pendis_miskin_rice'),
                ],
                'fisabilillah' => [
                    'amount' => $query->sum('t_pendis_fisabilillah_amount'),
                    'rice' => $query->sum('t_pendis_fisabilillah_rice'),
                ],
            ],
            'program' => [
                'kemanusiaan' => [
                    'amount' => $query->sum('t_pendis_kemanusiaan_amount'),
                    'rice' => $query->sum('t_pendis_kemanusiaan_rice'),
                ],
                'dakwah' => [
                    'amount' => $query->sum('t_pendis_dakwah_amount'),
                    'rice' => $query->sum('t_pendis_dakwah_rice'),
                ],
            ],
            'total_pm' => $query->sum('t_pm'),
        ];

        return response()->json($summary);
    }

    /**
     * Get monthly statistics grouped by periode.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyStats(Request $request)
    {
        $query = RekapPendis::query();

        // Date range filter
        if ($request->has('year')) {
            $year = $request->year;
            $query->whereRaw('YEAR(periode_date) = ?', [$year]);
        }

        // Filter by unit if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $monthlyData = $query->selectRaw("
            DATE_FORMAT(periode_date, '%Y-%m') as month,
            SUM(t_pendis_zf_amount) as pendis_zf_amount,
            SUM(t_pendis_zf_rice) as pendis_zf_rice,
            SUM(t_pendis_zm) as pendis_zm,
            SUM(t_pendis_ifs) as pendis_ifs,
            SUM(t_pendis_fakir_amount + t_pendis_miskin_amount + t_pendis_fisabilillah_amount) as total_asnaf_amount,
            SUM(t_pendis_kemanusiaan_amount + t_pendis_dakwah_amount) as total_program_amount,
            SUM(t_pm) as total_pm
        ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyData);
    }

    /**
     * Get distribution statistics by asnaf and program.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function distributionStats(Request $request)
    {
        $query = RekapPendis::query();

        // Filter by periode if provided
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Filter by year if provided
        if ($request->has('year')) {
            $year = $request->year;
            $query->whereRaw('YEAR(periode_date) = ?', [$year]);
        }

        // Filter by unit if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $totals = $query->selectRaw('
            SUM(t_pendis_fakir_amount) as fakir_amount,
            SUM(t_pendis_miskin_amount) as miskin_amount,
            SUM(t_pendis_fisabilillah_amount) as fisabilillah_amount,
            SUM(t_pendis_kemanusiaan_amount) as kemanusiaan_amount,
            SUM(t_pendis_dakwah_amount) as dakwah_amount,
            SUM(t_pendis_fakir_rice) as fakir_rice,
            SUM(t_pendis_miskin_rice) as miskin_rice,
            SUM(t_pendis_fisabilillah_rice) as fisabilillah_rice,
            SUM(t_pendis_kemanusiaan_rice) as kemanusiaan_rice,
            SUM(t_pendis_dakwah_rice) as dakwah_rice
        ')
            ->first()
            ->toArray();

        $stats = [
            'asnaf' => [
                'fakir' => [
                    'amount' => $totals['fakir_amount'],
                    'rice' => $totals['fakir_rice'],
                ],
                'miskin' => [
                    'amount' => $totals['miskin_amount'],
                    'rice' => $totals['miskin_rice'],
                ],
                'fisabilillah' => [
                    'amount' => $totals['fisabilillah_amount'],
                    'rice' => $totals['fisabilillah_rice'],
                ],
            ],
            'program' => [
                'kemanusiaan' => [
                    'amount' => $totals['kemanusiaan_amount'],
                    'rice' => $totals['kemanusiaan_rice'],
                ],
                'dakwah' => [
                    'amount' => $totals['dakwah_amount'],
                    'rice' => $totals['dakwah_rice'],
                ],
            ],
        ];

        return response()->json($stats);
    }
}
