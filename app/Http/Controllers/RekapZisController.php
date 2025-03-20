<?php

namespace App\Http\Controllers;

use App\Models\RekapZis;
use App\Http\Resources\RekapZisResource;
use App\Http\Resources\RekapZisCollection;
use Illuminate\Http\Request;

class RekapZisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = RekapZis::with('unit');

        // Filter by unit_id if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by period if provided
        if ($request->has('period')) {
            $query->where('period', $request->period);
        }

        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('period_date', [$request->from_date, $request->to_date]);
        }

        // Sort options
        $sortField = $request->input('sort_by', 'period_date');
        $sortOrder = $request->input('sort_order', 'desc');

        $rekapZis = $query->orderBy($sortField, $sortOrder)
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => RekapZisResource::collection($rekapZis),
            'meta' => [
                'total' => $rekapZis->total(),
                'per_page' => $rekapZis->perPage(),
                'current_page' => $rekapZis->currentPage(),
                'total_pages' => $rekapZis->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return \App\Http\Resources\RekapZisResource
     */
    public function show(RekapZis $rekapZis)
    {
        return new RekapZisResource($rekapZis->load('unit'));
    }

    /**
     * Get summary statistics for dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $query = RekapZis::query();

        // Filter by period if provided
        if ($request->has('period')) {
            $query->where('period', $request->period);
        }

        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('period_date', [$request->from_date, $request->to_date]);
        }

        // Filter by unit if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $summary = [
            'total_zf_amount' => $query->sum('total_zf_amount'),
            'total_zf_rice' => $query->sum('total_zf_rice'),
            'total_zf_muzakki' => $query->sum('total_zf_muzakki'),
            'total_zm_amount' => $query->sum('total_zm_amount'),
            'total_zm_muzakki' => $query->sum('total_zm_muzakki'),
            'total_ifs_amount' => $query->sum('total_ifs_amount'),
            'total_ifs_munfiq' => $query->sum('total_ifs_munfiq'),
        ];

        return response()->json($summary);
    }

    /**
     * Get monthly statistics grouped by period.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyStats(Request $request)
    {
        $query = RekapZis::query();

        // Date range filter
        if ($request->has('year')) {
            $year = $request->year;
            $query->whereRaw("YEAR(period_date) = ?", [$year]);
        }

        // Filter by unit if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $monthlyData = $query->selectRaw("
            DATE_FORMAT(period_date, '%Y-%m') as month,
            SUM(total_zf_amount) as zf_amount,
            SUM(total_zf_rice) as zf_rice,
            SUM(total_zm_amount) as zm_amount,
            SUM(total_ifs_amount) as ifs_amount
        ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyData);
    }
}
