<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\CourtResource;
use App\Http\Resources\JudiciaryFunctionResource;
use App\Models\Court;
use App\Models\JudiciaryFunction;
use Illuminate\Http\JsonResponse;

class JudiciaryController extends Controller
{
    public function index(): JsonResponse
    {
        $courts = Court::orderBy('sort_order')->get();
        $functions = JudiciaryFunction::orderBy('sort_order')->get();

        return $this->successResponse([
            'courts' => CourtResource::collection($courts),
            'functions' => JudiciaryFunctionResource::collection($functions),
        ]);
    }
}
