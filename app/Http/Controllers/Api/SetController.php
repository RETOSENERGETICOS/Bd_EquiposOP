<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Set;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetController extends Controller
{
    public function index(Request $request): JsonResponse {
        return response()->json(
            Set::all()->map( fn(Set $set) => $this->showSet($set) )
        );
    }

    private function showSet(Set $set): array {
        return [
            'id' => $set->id,
            'name' => $set->name
        ];
    }
}
