<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\RekapSetorResource;
use App\Models\RekapSetor;
use Illuminate\Http\Request;

class RekapSetorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = RekapSetor::query();

        // Filter by unit_id if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by periode if provided
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('periode_date', [$request->from_date, $request->to_date]);
        }

        // Load unit relationship if requested
        if ($request->has('with_unit') && $request->with_unit == 'true') {
            $query->with('unit');
        }

        // Sort results
        $sortField = $request->sort_by ?? 'periode_date';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $rekapSetors = $query->paginate($perPage);

        return response()->json([
            'data' => RekapSetorResource::collection($rekapSetors),
            'meta' => [
                'total' => $rekapSetors->total(),
                'per_page' => $rekapSetors->perPage(),
                'current_page' => $rekapSetors->currentPage(),
                'total_pages' => $rekapSetors->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(RekapSetorResource $rekapSetor)
    {
        return new RekapSetorResource($rekapSetor->load('unit'));
    }

    /**
     * Get total statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        $query = RekapSetor::query();

        // Filter by unit_id if provided
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by periode if provided
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('periode_date', [$request->from_date, $request->to_date]);
        }

        $stats = [
            'total_zf_amount' => $query->sum('t_setor_zf_amount'),
            'total_zf_rice' => $query->sum('t_setor_zf_rice'),
            'total_zm' => $query->sum('t_setor_zm'),
            'total_ifs' => $query->sum('t_setor_ifs'),
            'count' => $query->count(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
