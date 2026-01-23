<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllocationConfigRequest;
use App\Http\Resources\AllocationConfigResource;
use App\Models\AllocationConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AllocationConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = AllocationConfig::query();

        if ($request->has('zis_type')) {
            $query->where('zis_type', $request->zis_type);
        }

        if ($request->has('effective_year')) {
            $query->where('effective_year', $request->effective_year);
        }

        $data = $query->orderBy('effective_year', 'desc')
            ->orderBy('zis_type')
            ->get();

        return AllocationConfigResource::collection($data);
    }

    public function store(AllocationConfigRequest $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $config = AllocationConfig::create($validated);

        return (new AllocationConfigResource($config))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AllocationConfig $allocationConfig)
    {
        return new AllocationConfigResource($allocationConfig);
    }

    public function update(AllocationConfigRequest $request, AllocationConfig $allocationConfig)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $allocationConfig->update($validated);

        return new AllocationConfigResource($allocationConfig);
    }

    public function destroy(AllocationConfig $allocationConfig)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $allocationConfig->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get active config for specific ZIS type and year
     */
    public function getActive(Request $request)
    {
        $request->validate([
            'zis_type' => 'required|in:zf,zm,ifs',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        $year = $request->year ?? now()->year;

        $config = AllocationConfig::where('zis_type', $request->zis_type)
            ->where('effective_year', '<=', $year)
            ->orderBy('effective_year', 'desc')
            ->first();

        if (!$config) {
            return response()->json([
                'message' => 'No configuration found, using defaults',
                'data' => [
                    'zis_type' => $request->zis_type,
                    'effective_year' => $year,
                    'setor_percentage' => AllocationConfig::DEFAULT_SETOR,
                    'kelola_percentage' => AllocationConfig::DEFAULT_KELOLA,
                    'amil_percentage' => $request->zis_type === 'ifs'
                        ? AllocationConfig::DEFAULT_AMIL_IFS
                        : AllocationConfig::DEFAULT_AMIL_ZF_ZM,
                    'is_default' => true,
                ]
            ]);
        }

        return new AllocationConfigResource($config);
    }
}
