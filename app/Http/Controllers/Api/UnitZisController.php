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
    public function index(Request $request)
    {
        // Mengambil user yang sedang login
        $user = $request->user();

        // Mengambil semua unitZis yang berelasi dengan user yang sedang login
        // Tambahkan eager loading untuk village dan district
        $units = UnitZis::where('user_id', $user->id)
            ->with(['village', 'district'])
            ->get();

        // Mengembalikan response dengan format yang konsisten
        if ($units->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data UPZ Tidak Tersedia',
                'data' => []
            ], 200);
        }

        return new UnitZisResource(true, 'List Data UPZ', $units);
    }

    public function show($id)
    {
        // Tambahkan eager loading untuk village dan district
        $unitZis = UnitZis::with(['village', 'district'])->findOrFail($id);

        return new UnitZisResource(true, 'Detail Data UPZ', $unitZis);
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
            'address' => 'required|string',
            'unit_leader' => 'required|string',
            'unit_assistant' => 'required|string',
            'unit_finance' => 'required|string',
            'operator_phone' => 'required|string',
            'rice_price' => 'required|integer',
            'is_verified' => 'boolean'
        ]);

        // Membuat produk baru    
        $unitZis = UnitZis::create($validatedData);

        // Load relasi setelah membuat unitZis
        $unitZis->load(['village', 'district']);

        return new UnitZisResource(true, 'UPZ Baru Berhasil dibuat', $unitZis);
    }

    public function destroy($id)
    {
        $unitZis = UnitZis::findOrFail($id);
        $unitZis->delete();

        return response()->json(['message' => 'Unit ZIS deleted successfully'], 200);
    }
}
