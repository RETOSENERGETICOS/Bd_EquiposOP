<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    public function index(Request $request): JsonResponse {
        return response()->json(
            Calibration::all()->map( fn(Calibration $calibration) => $this->showBrand($calibration) )
        );
    }

    private function showCalibration(Calibration $calibration): array {
        return [
            'id' => $calibration->id,
            'name' => $calibration->name
        ];
    }
}
