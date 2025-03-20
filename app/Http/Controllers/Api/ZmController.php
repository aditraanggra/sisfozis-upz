<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zm;
use App\Models\UnitZis;
use App\Http\Resources\ZmResource;
use App\Http\Requests\ZmRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Zm::with(['unit'])
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle search if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('muzakki_name', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Handle date range filter if needed
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        $data = $query->latest('trx_date')->get();
        /* ->paginate($request->per_page ?? 15)
            ->appends($request->query()); */

        return ZmResource::collection($data)->response()->getData(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ZmRequest $request)
    {
        try {
            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            // Create transaction
            $Zm = Zm::create($validated);

            // Return response with the created resource
            return (new ZmResource($Zm->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating Zm transaction',
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
            $Zm = Zm::with('unit')->findOrFail($id);

            // Check if user can access this record
            if (!Auth::user()->isAdmin() && $Zm->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new ZmResource($Zm);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving Zm transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ZmRequest $request, string $id)
    {
        try {
            $Zm = Zm::findOrFail($id);

            // Check if user can update this record
            if (!Auth::user()->isAdmin() && $Zm->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $Zm->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            // Update transaction
            $Zm->update($validated);

            return new ZmResource($Zm->load('unit'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating Zm transaction',
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
            $Zm = Zm::findOrFail($id);

            // Check if user can delete this record
            if (!Auth::user()->isAdmin() && $Zm->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $Zm->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting Zm transaction',
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
