<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IfsRequest;
use App\Http\Resources\IfsResource;
use App\Models\Ifs;
use App\Models\UnitZis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IfsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ifs::with(['unit'])
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle search if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('munfiq_name', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Handle date range filter if needed
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        // Handle total_munfiq filter if needed
        if ($request->has('total_munfiq')) {
            $query->where('total_munfiq', $request->total_munfiq);
        }

        // Handle total_munfiq range filter
        if ($request->has('min_munfiq')) {
            $query->where('total_munfiq', '>=', $request->min_munfiq);
        }
        if ($request->has('max_munfiq')) {
            $query->where('total_munfiq', '<=', $request->max_munfiq);
        }

        // Handle sorting by total_munfiq
        if ($request->has('sort_by') && $request->sort_by === 'total_munfiq') {
            $direction = $request->get('sort_direction', 'desc');
            $query->orderBy('total_munfiq', $direction);
        }

        // Default sorting
        if (! $request->has('sort_by')) {
            $query->latest('trx_date');
        }

        // Handle year filter
        if ($request->has('year') && $request->year !== 'all') {
            $year = (int) $request->year;
            $query->whereYear('trx_date', $year);
        }

        // Handle pagination
        $perPage = $request->get('per_page', 15);
        $data = $perPage > 0 ? $query->paginate($perPage)->appends($request->query()) : $query->get();

        return IfsResource::collection($data)->response()->getData(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IfsRequest $request)
    {
        try {
            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            // Create transaction
            $ifs = Ifs::create($validated);

            // Return response with created resource
            return (new IfsResource($ifs->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating IFS transaction',
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
            $ifs = Ifs::with('unit')->findOrFail($id);

            // Check if user can access this record
            if (! User::currentIsAdmin() && $ifs->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new IfsResource($ifs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving IFS transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IfsRequest $request, string $id)
    {
        try {
            $ifs = Ifs::findOrFail($id);

            // Check if user can update this record
            if (! User::currentIsAdmin() && $ifs->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $ifs->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            // Update transaction
            $ifs->update($validated);

            return new IfsResource($ifs->load('unit'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating IFS transaction',
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
            $ifs = Ifs::findOrFail($id);

            // Check if user can delete this record
            if (! User::currentIsAdmin() && $ifs->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $ifs->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting IFS transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get statistics for IFS transactions
     */
    public function statistics(Request $request)
    {
        $query = Ifs::query()
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Apply same filters as index
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        // Handle year filter for statistics
        if ($request->has('year') && $request->year !== 'all') {
            $year = (int) $request->year;
            $query->whereYear('trx_date', $year);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_munfiq' => $query->sum('total_munfiq'),
            'average_amount' => $query->avg('amount'),
            'average_munfiq' => $query->avg('total_munfiq'),
            'highest_munfiq' => $query->max('total_munfiq'),
            'individual_donors' => $query->where('total_munfiq', 1)->count(),
            'group_donors' => $query->where('total_munfiq', '>', 1)->count(),
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
