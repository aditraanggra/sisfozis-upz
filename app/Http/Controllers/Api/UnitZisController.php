<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitZisResource;
use Illuminate\Http\Request;
use App\Models\UnitZis;

class UnitZisController extends Controller
{
    /**
     * Menampilkan daftar semua produk
     * Method ini dipanggil ketika mengakses GET /products
     */
    public function index()
    {
        // Mengambil semua produk dan mengubahnya menjadi collection
        $unit = UnitZis::all();

        // Mengembalikan response dengan format yang konsisten
        return new UnitZisResource(true, 'List Data UPZ', $unit);
    }

    public function store(Request $request)
    {
        // Validasi input dari user
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

        // Membuat produk baru    
        $unitZis = UnitZis::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $unitZis,
            'message' => 'UPZ Baru Berhasil dibuat'
        ], 201);
    }

    public function destroy($id)
    {
        $unitZis = UnitZis::findOrFail($id);
        $unitZis->delete();

        return response()->json(['message' => 'Unit ZIS deleted successfully'], 200);
    }
}
