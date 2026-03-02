<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LpzRequest;
use App\Http\Resources\LpzResource;
use App\Models\Lpz;
use App\Models\UnitZis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LpzController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lpz::with(['unit'])
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle year filter
        if ($request->has('year') && $request->year !== 'all') {
            $year = (int) $request->year;
            $query->where('lpz_year', $year);
        }

        // Handle date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('trx_date', [$request->start_date, $request->end_date]);
        }

        $data = $query->latest('trx_date')->get();

        return LpzResource::collection($data)->response()->getData(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LpzRequest $request)
    {
        try {
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            $lpz = Lpz::create($validated);

            return (new LpzResource($lpz->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error creating LPZ record', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Error creating LPZ record',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $lpz = Lpz::with('unit')->findOrFail($id);

            // Check if user can access this record
            if (! User::currentIsAdmin() && $lpz->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Error retrieving LPZ record',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            \Log::error('Error retrieving LPZ record', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error retrieving LPZ record',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LpzRequest $request, string $id)
    {
        try {
            $lpz = Lpz::findOrFail($id);

            // Check if user can update this record
            if (! User::currentIsAdmin() && $lpz->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $lpz->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            $lpz->update($validated);

            return new LpzResource($lpz->load('unit'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating LPZ record',
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
            $lpz = Lpz::findOrFail($id);

            // Check if user can delete this record
            if (! User::currentIsAdmin() && $lpz->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $lpz->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting LPZ record',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
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
