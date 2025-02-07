<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitZis;

class UnitZisController extends Controller
{
    //
    public function index()
    {
        return response()->json(UnitZis::with(['user', 'category', 'village', 'district'])->get(), 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:unit_categories,id',
            'village_id' => 'required|exists:villages,id',
            'district_id' => 'required|exists:districts,id',
            'no_sk' => 'required|string',
            'unit_name' => 'required|string',
            'no_register' => 'required|string',
            'unit_field' => 'required|string',
            'address' => 'required|string',
            'unit_leader' => 'required|string',
            'unit_assistant' => 'required|string',
            'unit_finance' => 'required|string',
            'operator_name' => 'required|string',
            'operator_phone' => 'required|string',
            'rice_price' => 'required|integer',
            'is_verified' => 'boolean'
        ]);

        $unitZis = UnitZis::create($validatedData);

        return response()->json($unitZis, 201);
    }

    public function show($id)
    {
        $unitZis = UnitZis::with(['user', 'category', 'village', 'district'])->findOrFail($id);
        return response()->json($unitZis, 200);
    }

    public function update(Request $request, $id)
    {
        $unitZis = UnitZis::findOrFail($id);

        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:unit_categories,id',
            'village_id' => 'sometimes|exists:villages,id',
            'district_id' => 'sometimes|exists:districts,id',
            'no_sk' => 'sometimes|string',
            'unit_name' => 'sometimes|string',
            'no_register' => 'sometimes|string',
            'unit_field' => 'sometimes|string',
            'address' => 'sometimes|string',
            'unit_leader' => 'sometimes|string',
            'unit_assistant' => 'sometimes|string',
            'unit_finance' => 'sometimes|string',
            'operator_name' => 'sometimes|string',
            'operator_phone' => 'sometimes|string',
            'rice_price' => 'sometimes|integer',
            'is_verified' => 'boolean'
        ]);

        $unitZis->update($validatedData);

        return response()->json($unitZis, 200);
    }

    public function destroy($id)
    {
        $unitZis = UnitZis::findOrFail($id);
        $unitZis->delete();

        return response()->json(['message' => 'Unit ZIS deleted successfully'], 200);
    }
}
