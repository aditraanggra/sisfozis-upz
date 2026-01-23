<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZfPaymentTypeRequest;
use App\Http\Resources\ZfPaymentTypeResource;
use App\Models\User;
use App\Models\ZfPaymentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ZfPaymentTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ZfPaymentType::query();

        // Default: hanya tampilkan yang aktif, kecuali ada param all=true
        if (!$request->boolean('all')) {
            $query->active();
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $data = $query->orderBy('type')->orderBy('name')->get();

        return ZfPaymentTypeResource::collection($data);
    }

    public function store(ZfPaymentTypeRequest $request)
    {
        // Check admin access
        if (!User::currentIsAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $paymentType = ZfPaymentType::create($validated);

        return (new ZfPaymentTypeResource($paymentType))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ZfPaymentType $zfPaymentType)
    {
        return new ZfPaymentTypeResource($zfPaymentType);
    }

    public function update(ZfPaymentTypeRequest $request, ZfPaymentType $zfPaymentType)
    {
        if (!User::currentIsAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $zfPaymentType->update($validated);

        return new ZfPaymentTypeResource($zfPaymentType);
    }

    public function destroy(ZfPaymentType $zfPaymentType)
    {
        if (!User::currentIsAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $zfPaymentType->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
