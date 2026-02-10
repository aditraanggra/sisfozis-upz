<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZmRequest;
use App\Http\Resources\ZmResource;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Zm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ZmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Zm::with(['unit'])
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle search if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('muzakki_name', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Handle phone number filter if needed
        if ($request->has('no_telp')) {
            $query->where('no_telp', $request->no_telp);
        }

        // Handle date range filter if needed
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        // Handle sorting by phone number
        if ($request->has('sort_by') && $request->sort_by === 'no_telp') {
            $direction = $request->get('sort_direction', 'desc');
            $query->orderBy('no_telp', $direction);
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
                'error' => $e->getMessage(),
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
            if (! User::currentIsAdmin() && $Zm->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new ZmResource($Zm);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving Zm transaction',
                'error' => $e->getMessage(),
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
            if (! User::currentIsAdmin() && $Zm->unit->user_id !== Auth::id()) {
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
                'error' => $e->getMessage(),
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
            if (! User::currentIsAdmin() && $Zm->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $Zm->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting Zm transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get statistics for ZM transactions
     */
    public function statistics(Request $request)
    {
        $query = Zm::query()
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Apply same filters as index
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_with_phone' => $query->whereNotNull('no_telp')->count(),
            'total_without_phone' => $query->whereNull('no_telp')->count(),
            'average_amount' => $query->avg('amount'),
            'highest_amount' => $query->max('amount'),
            'phone_coverage' => $query->count() > 0 ? round(($query->whereNotNull('no_telp')->count() / $query->count()) * 100, 2) : 0,
        ];

        return response()->json($stats);
    }

    /**
     * Check if authenticated user owns unit or is admin
     */
    private function checkUnitOwnership(int $unitId): void
    {
        $unit = UnitZis::findOrFail($unitId);

        if (! User::currentIsAdmin() && $unit->user_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to use this unit');
        }
    }
}
