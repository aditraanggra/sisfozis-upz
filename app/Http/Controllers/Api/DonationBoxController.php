<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationBoxRequest;
use App\Http\Resources\DonationBoxResource;
use App\Models\DonationBox;
use App\Models\UnitZis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DonationBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DonationBox::with(['unit'])
            ->when(! User::currentIsAdmin(), function ($query) {
                return $query->whereHas('unit', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            });

        // Handle search if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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

        return DonationBoxResource::collection($data)->response()->getData(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DonationBoxRequest $request)
    {
        try {
            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership
            $this->checkUnitOwnership($validated['unit_id']);

            // Create transaction
            $DonationBox = DonationBox::create($validated);

            // Return response with the created resource
            return (new DonationBoxResource($DonationBox->load('unit')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            report($e); // Log the actual error

            return response()->json([
                'message' => 'Error creating DonationBox transaction',
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $DonationBox = DonationBox::with('unit')->findOrFail($id);

            // Ensure unit exists before accessing relationship
            if ($DonationBox->unit === null) {
                abort(Response::HTTP_NOT_FOUND, 'Donation box unit not found');
            }

            // Check if user can access this record
            if (! User::currentIsAdmin() && $DonationBox->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            return new DonationBoxResource($DonationBox);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Donation box not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving DonationBox transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DonationBoxRequest $request, string $id)
    {
        try {
            $DonationBox = DonationBox::with('unit')->findOrFail($id);

            // Ensure unit exists before accessing relationship
            if ($DonationBox->unit === null) {
                abort(Response::HTTP_NOT_FOUND, 'Donation box unit not found');
            }

            // Check if user can update this record
            if (! User::currentIsAdmin() && $DonationBox->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            // Validate and get data
            $validated = $request->validated();

            // Check unit ownership if unit_id is being updated
            if (isset($validated['unit_id']) && $validated['unit_id'] !== $DonationBox->unit_id) {
                $this->checkUnitOwnership($validated['unit_id']);
            }

            // Update transaction
            $DonationBox->update($validated);

            return new DonationBoxResource($DonationBox->load('unit'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Donation box not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating DonationBox transaction',
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
            $DonationBox = DonationBox::with('unit')->findOrFail($id);

            // Ensure unit exists before accessing relationship
            if ($DonationBox->unit === null) {
                abort(Response::HTTP_NOT_FOUND, 'Donation box unit not found');
            }

            // Check if user can delete this record
            if (! User::currentIsAdmin() && $DonationBox->unit->user_id !== Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
            }

            $DonationBox->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Donation box not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting DonationBox transaction',
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
