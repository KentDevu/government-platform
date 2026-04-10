<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\LeaderResource;
use App\Models\Department;
use App\Models\Leader;
use Illuminate\Http\JsonResponse;

class ExecutiveController extends Controller
{
    public function index(): JsonResponse
    {
        $leaders = Leader::orderBy('sort_order')->get();
        $departments = Department::orderBy('sort_order')->get();

        return $this->successResponse([
            'leaders' => LeaderResource::collection($leaders),
            'departments' => DepartmentResource::collection($departments),
        ]);
    }
}
