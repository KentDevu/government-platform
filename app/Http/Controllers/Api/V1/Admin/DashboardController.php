<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\Controller;
use App\Models\Agency;
use App\Models\Announcement;
use App\Models\Chamber;
use App\Models\Court;
use App\Models\Department;
use App\Models\JudiciaryFunction;
use App\Models\Leader;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = [
            'services' => Service::count(),
            'announcements' => Announcement::count(),
            'press_releases' => PressRelease::count(),
            'agencies' => Agency::count(),
            'leaders' => Leader::count(),
            'departments' => Department::count(),
            'chambers' => Chamber::count(),
            'recent_laws' => RecentLaw::count(),
            'courts' => Court::count(),
            'judiciary_functions' => JudiciaryFunction::count(),
        ];

        return $this->successResponse($stats);
    }
}
