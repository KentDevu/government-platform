<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\HeroSettingResource;
use App\Http\Resources\PressReleaseResource;
use App\Http\Resources\ServiceResource;
use App\Models\Announcement;
use App\Models\HeroSetting;
use App\Models\PressRelease;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    public function index(): JsonResponse
    {
        $hero = HeroSetting::first();
        $services = Service::with('department')
            ->where('page', 'landing')
            ->orderBy('sort_order')
            ->get();
        $announcements = Announcement::orderBy('sort_order')->get();
        $pressReleases = PressRelease::orderBy('sort_order')->get();

        return $this->successResponse([
            'hero' => $hero ? new HeroSettingResource($hero) : null,
            'services' => ServiceResource::collection($services),
            'announcements' => AnnouncementResource::collection($announcements),
            'press_releases' => PressReleaseResource::collection($pressReleases),
        ]);
    }
}
