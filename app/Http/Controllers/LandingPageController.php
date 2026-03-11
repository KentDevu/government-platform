<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\AgencyGroup;
use App\Models\Announcement;
use App\Models\Chamber;
use App\Models\Court;
use App\Models\Department;
use App\Models\HeroSetting;
use App\Models\JudiciaryFunction;
use App\Models\Leader;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use App\Models\Service;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $heroModel = HeroSetting::first();
        $hero = $heroModel ? $heroModel->toArray() : [
            'badge' => 'Official Government Portal',
            'title' => 'The Gateway to',
            'highlight' => 'Citizen Services',
            'description' => 'Access unified government information, digital services, and national updates in one secure location.',
            'image' => '/assets/img/assets/home.jpg',
        ];

        $services = Service::with('department')->where('page', 'landing')->orderBy('sort_order')->get();
        $announcements = Announcement::orderBy('sort_order')->get()->map(function ($a) {
            return [
                'category' => $a->category,
                'categoryColor' => $a->category_color,
                'title' => $a->title,
                'excerpt' => $a->excerpt,
                'date' => $a->date,
                'image' => $a->image,
                'imageAlt' => $a->image_alt,
            ];
        })->toArray();

        $pressReleases = PressRelease::orderBy('sort_order')->get()->toArray();

        return view('landing', compact('hero', 'services', 'announcements', 'pressReleases'));
    }

    public function services()
    {
        $pageTitle = 'Government Services';
        $pageDescription = 'Browse and access digital services offered by Philippine government agencies. From document processing to online payments.';
        $heroImage = '/assets/img/assets/services.jpeg';

        $services = Service::with('department')->where('page', 'services')->orderBy('sort_order')->get();

        return view('pages.services', compact('pageTitle', 'pageDescription', 'heroImage', 'services'));
    }

    public function agencies()
    {
        $pageTitle = 'Government Agencies';
        $pageDescription = 'Complete directory of Philippine government departments, bureaus, and attached agencies.';
        $heroImage = '/assets/img/assets/agencies.jpeg';

        $agencyGroups = AgencyGroup::with('agencies')->orderBy('sort_order')->get()->map(function ($group) {
            return [
                'category' => $group->category,
                'agencies' => $group->agencies->map(function ($a) {
                    return ['name' => $a->name, 'acronym' => $a->acronym, 'icon' => $a->icon, 'url' => $a->url];
                })->toArray(),
            ];
        })->toArray();

        return view('pages.agencies', compact('pageTitle', 'pageDescription', 'heroImage', 'agencyGroups'));
    }

    public function executive()
    {
        $pageTitle = 'The Executive Branch';
        $pageDescription = 'The Executive branch carries out and enforces laws. It includes the President, Vice President, Cabinet, executive departments, and independent agencies.';
        $heroImage = '/assets/img/assets/executive.jpeg';

        $leaders = Leader::orderBy('sort_order')->get()->toArray();
        $departments = Department::orderBy('sort_order')->get()->toArray();

        return view('pages.executive', compact('pageTitle', 'pageDescription', 'heroImage', 'leaders', 'departments'));
    }

    public function legislative()
    {
        $pageTitle = 'The Legislative Branch';
        $pageDescription = 'The Legislative branch, also known as Congress, is the law-making body of the Philippine government. It is a bicameral legislature composed of the Senate and the House of Representatives.';
        $heroImage = '/assets/img/assets/legislative.jpeg';

        $chambers = Chamber::orderBy('sort_order')->get()->toArray();
        $recentLaws = RecentLaw::orderBy('sort_order')->get()->toArray();

        return view('pages.legislative', compact('pageTitle', 'pageDescription', 'heroImage', 'chambers', 'recentLaws'));
    }

    public function judiciary()
    {
        $pageTitle = 'The Judiciary Branch';
        $pageDescription = 'The Judicial branch interprets the laws of the Philippines. It is vested in one Supreme Court and in such lower courts as may be established by law.';
        $heroImage = '/assets/img/assets/judiciary.jpeg';

        $courts = Court::orderBy('sort_order')->get()->toArray();
        $functions = JudiciaryFunction::orderBy('sort_order')->get()->toArray();

        return view('pages.judiciary', compact('pageTitle', 'pageDescription', 'heroImage', 'courts', 'functions'));
    }
}
