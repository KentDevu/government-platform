<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\AgencyGroupResource;
use App\Models\AgencyGroup;
use Illuminate\Http\JsonResponse;

class AgencyController extends Controller
{
    public function index(): JsonResponse
    {
        $agencyGroups = AgencyGroup::with('agencies')
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse(
            AgencyGroupResource::collection($agencyGroups),
        );
    }
}
