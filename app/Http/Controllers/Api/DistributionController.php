<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\UnitZis;
use App\Http\Resources\DistributionResource;
use App\Http\Requests\DistributionRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Distribution::with(['unit'])
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle search if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mustahik_name', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('fund_type', 'like', "%{$search}%")
                    ->orWhere('asnaf', 'like', "%{$search}%")
                    ->orWhere('program', 'like', "%{$search}%");
            });
        }

        // Handle date range filter if needed
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        $data = $query->latest('trx_date')
            ->paginate($request->per_page ?? 15)
            ->appends($request->query());

        return DistributionResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DistributionRequest $request)
    {
        try {
            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            // Create transaction
            $Distribution = Distribution::create($validated);

            // Return response with the created resource
            return (new DistributionResource($Distribution->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating Distribution transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $Distribution = Distribution::with('unit')->findOrFail($id);

            // Check if user can access this record
            if (!Auth::user()->isAdmin() && $Distribution->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new DistributionResource($Distribution);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving Distribution transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DistributionRequest $request, string $id)
    {
        try {
            $Distribution = Distribution::findOrFail($id);

            // Check if user can update this record
            if (!Auth::user()->isAdmin() && $Distribution->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $Distribution->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            // Update transaction
            $Distribution->update($validated);

            return new DistributionResource($Distribution->load('unit'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating Distribution transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Distribution = Distribution::findOrFail($id);

            // Check if user can delete this record
            if (!Auth::user()->isAdmin() && $Distribution->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $Distribution->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting Distribution transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Check if authenticated user owns the unit or is admin
     */
    private function checkUnitOwnership(int $unitId): void
    {
        $unit = UnitZis::findOrFail($unitId);

        if (!Auth::user()->isAdmin() && $unit->user_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to use this unit');
        }
    }
}
