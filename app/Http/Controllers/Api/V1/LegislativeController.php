<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ChamberResource;
use App\Http\Resources\RecentLawResource;
use App\Models\Chamber;
use App\Models\RecentLaw;
use Illuminate\Http\JsonResponse;

class LegislativeController extends Controller
{
    public function index(): JsonResponse
    {
        $chambers = Chamber::orderBy('sort_order')->get();
        $recentLaws = RecentLaw::orderBy('sort_order')->get();

        return $this->successResponse([
            'chambers' => ChamberResource::collection($chambers),
            'recent_laws' => RecentLawResource::collection($recentLaws),
        ]);
    }
}
