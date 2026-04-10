<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $services = Service::with('department')
            ->where('page', 'services')
            ->orderBy('sort_order')
            ->paginate($perPage);

        return $this->paginatedResponse(
            ServiceResource::collection($services),
            $services,
        );
    }
}
