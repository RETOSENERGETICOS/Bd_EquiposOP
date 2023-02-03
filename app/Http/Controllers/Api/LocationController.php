<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse {
        return response()->json(
            Location::all()->map( fn(Location $location) => $this->showLocation($location) )
        );
    }

    private function showLocation(Location $location): array {
        return [
            'id' => $location->id,
            'name' => $location->name
        ];
    }
}
