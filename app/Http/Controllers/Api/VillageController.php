<?php

namespace App\Http\Controllers\Api;

use App\Models\Village;
use App\Http\Controllers\Controller;
use App\Http\Resources\VillageResorces;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    /**
     * index
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        // Cek apakah parameter district_id ada di URL
        if ($request->has('district_id')) {
            // Jika ada, ambil data desa berdasarkan district_id
            $data = Village::where('district_id', $request->district_id)->get();
        } else {
            // Jika tidak ada parameter, ambil semua data desa
            $data = Village::all();
        }

        // Return collection of villages as a resource
        return new VillageResorces(true, 'List Data Desa', $data);
    }
}
