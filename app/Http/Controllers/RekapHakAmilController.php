<?php

namespace App\Http\Controllers;

use App\Http\Resources\RekapHakAmilResource;
use App\Models\RekapHakAmil;
use Illuminate\Http\Request;

class RekapHakAmilController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = RekapHakAmil::with('unit');

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

        $rekapHakAmil = $query->orderBy($sortField, $sortOrder)
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => RekapHakAmilResource::collection($rekapHakAmil),
            'meta' => [
                'total' => $rekapHakAmil->total(),
                'per_page' => $rekapHakAmil->perPage(),
                'current_page' => $rekapHakAmil->currentPage(),
                'total_pages' => $rekapHakAmil->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\RekapHakAmilResource
     */
    public function show(RekapHakAmil $rekapHakAmil)
    {
        return new RekapHakAmilResource($rekapHakAmil->load('unit'));
    }
}
