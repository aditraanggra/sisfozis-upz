<?php

namespace App\Http\Controllers;

use App\Http\Resources\RekapAlokasiResource;
use App\Models\RekapAlokasi;
use Illuminate\Http\Request;

class RekapAlokasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = RekapAlokasi::with('unit');

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

        $rekapAlokasi = $query->orderBy($sortField, $sortOrder)
            ->paginate($request->input('per_page', 15));

        return RekapAlokasiResource::collection($rekapAlokasi);

        return response()->json([
            'data' => RekapAlokasiResource::collection($rekapAlokasi),
            'meta' => [
                'total' => $rekapAlokasi->total(),
                'per_page' => $rekapAlokasi->perPage(),
                'current_page' => $rekapAlokasi->currentPage(),
                'total_pages' => $rekapAlokasi->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\RekapAlokasiResource
     */
    public function show(RekapAlokasi $rekapAlokasi)
    {
        return new RekapAlokasiResource($rekapAlokasi->load('unit'));
    }

    /**
     * Get summary statistics for dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $query = RekapAlokasi::query();

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
            'total_setor' => [
                'zf_amount' => $query->sum('total_setor_zf_amount'),
                'zf_rice' => $query->sum('total_setor_zf_rice'),
                'zm' => $query->sum('total_setor_zm'),
                'ifs' => $query->sum('total_setor_ifs'),
            ],
            'total_kelola' => [
                'zf_amount' => $query->sum('total_kelola_zf_amount'),
                'zf_rice' => $query->sum('total_kelola_zf_rice'),
                'zm' => $query->sum('total_kelola_zm'),
                'ifs' => $query->sum('total_kelola_ifs'),
            ],
            'hak_amil' => [
                'zf_amount' => $query->sum('hak_amil_zf_amount'),
                'zf_rice' => $query->sum('hak_amil_zf_rice'),
                'zm' => $query->sum('hak_amil_zm'),
                'ifs' => $query->sum('hak_amil_ifs'),
            ],
            'alokasi_pendis' => [
                'zf_amount' => $query->sum('alokasi_pendis_zf_amount'),
                'zf_rice' => $query->sum('alokasi_pendis_zf_rice'),
                'zm' => $query->sum('alokasi_pendis_zm'),
                'ifs' => $query->sum('alokasi_pendis_ifs'),
            ],
            'hak_op' => [
                'zf_amount' => $query->sum('hak_op_zf_amount'),
                'zf_rice' => $query->sum('hak_op_zf_rice'),
            ],
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
        $query = RekapAlokasi::query();

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
            SUM(total_setor_zf_amount) as setor_zf_amount,
            SUM(total_setor_zf_rice) as setor_zf_rice,
            SUM(total_setor_zm) as setor_zm,
            SUM(total_setor_ifs) as setor_ifs,
            SUM(total_kelola_zf_amount) as kelola_zf_amount,
            SUM(total_kelola_zf_rice) as kelola_zf_rice,
            SUM(alokasi_pendis_zf_amount) as pendis_zf_amount,
            SUM(alokasi_pendis_zf_rice) as pendis_zf_rice
        ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyData);
    }
}
