<?php

namespace App\Http\Controllers\Api;

use App\Models\Village;

use App\Http\Controllers\Controller;

use App\Http\Resources\VillageResorces;

class VillageController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all villages
        $data = Village::all();

        //return collection of posts as a resource
        return new VillageResorces(true, 'List Data Desa', $data);
    }
}
