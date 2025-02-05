<?php

namespace App\Http\Controllers\Api;

use App\Models\District;

use App\Http\Controllers\Controller;

use App\Http\Resources\DistrictResource;

class DistrictController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all posts
        $districts = District::all();

        //return collection of posts as a resource
        return new DistrictResource(true, 'List Data Kecamatan', $districts);
    }
}
