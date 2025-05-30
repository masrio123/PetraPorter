<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\TenantLocation;
use App\Http\Controllers\Controller;

class TenantLocationController extends Controller
{
    public function index()
    {
        $locations = TenantLocation::all(['id', 'location_name']);
        return response()->json($locations);
    }
}
