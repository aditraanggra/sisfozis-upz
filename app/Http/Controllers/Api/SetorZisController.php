<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetorZisRequest;
use App\Http\Resources\SetorZisResource;
use App\Models\SetorZis;
use App\Models\UnitZis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SetorZisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SetorZis::with(['unit'])
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

        // Handle year filter
        if ($request->has('year') && $request->year !== 'all') {
            $year = (int) $request->year;
            $query->whereYear('trx_date', $year);
        }

        $data = $query->latest('trx_date')->get();

        /* ->paginate($request->per_page ?? 15)
            ->appends($request->query()); */
        return SetorZisResource::collection($data)->response()->getData(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SetorZisRequest $request)
    {
        try {
            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            // Create transaction
            $SetorZis = SetorZis::create($validated);

            // Return response with the created resource
            return (new SetorZisResource($SetorZis->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            \Log::error('Error creating SetorZis transaction', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error creating SetorZis transaction',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $SetorZis = SetorZis::with('unit')->findOrFail($id);

            // Check if user can access this record
            if (! User::currentIsAdmin() && $SetorZis->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new SetorZisResource($SetorZis);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving SetorZis transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SetorZisRequest $request, string $id)
    {
        try {
            $SetorZis = SetorZis::findOrFail($id);

            // Check if user can update this record
            if (! User::currentIsAdmin() && $SetorZis->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $SetorZis->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            // Update transaction
            $SetorZis->update($validated);

            return new SetorZisResource($SetorZis->load('unit'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating SetorZis transaction',
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
            $SetorZis = SetorZis::findOrFail($id);

            // Check if user can delete this record
            if (! User::currentIsAdmin() && $SetorZis->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $SetorZis->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting SetorZis transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get rice consolidation data — unsold rice grouped by destination.
     */
    public function riceConsolidation(Request $request)
    {
        $query = SetorZis::with(['unit', 'unit.village', 'unit.district'])
            ->unsoldRice();

        // Scope by role
        if (User::currentIsAdmin()) {
            // Admin sees all data - no filter
        } elseif (User::currentIsUpzKecamatan()) {
            $user = Auth::user();
            $query->whereHas('unit', fn($q) => $q->where('district_id', $user->district_id));
        } elseif (User::currentIsUpzDesa()) {
            $user = Auth::user();
            $query->whereHas('unit', fn($q) => $q->where('village_id', $user->village_id));
        } else {
            // Fallback: restrict to user's own units
            $query->whereHas('unit', fn($q) => $q->where('user_id', Auth::id()));
        }

        // Year filter
        if ($request->has('year') && $request->year !== 'all') {
            $query->whereYear('trx_date', (int) $request->year);
        }

        $records = $query->get();

        $totalUnsoldRice = $records->sum('zf_rice_deposit');

        $byDestination = $records->groupBy('deposit_destination')->map(function ($group, $destination) {
            return [
                'total_rice' => $group->sum('zf_rice_deposit'),
                'total_records' => $group->count(),
                'units' => $group->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'unit_id' => $record->unit_id,
                        'unit_name' => $record->unit?->unit_name,
                        'village' => $record->unit?->village?->name,
                        'district' => $record->unit?->district?->name,
                        'rice_kg' => $record->zf_rice_deposit,
                        'trx_date' => $record->trx_date?->format('Y-m-d'),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'total_unsold_rice' => $totalUnsoldRice,
            'by_destination' => $byDestination,
        ]);
    }

    /**
     * Check if authenticated user owns the unit or is admin
     */
    private function checkUnitOwnership(int $unitId): void
    {
        $unit = UnitZis::findOrFail($unitId);

        if (! User::currentIsAdmin() && $unit->user_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to use this unit');
        }
    }
}
