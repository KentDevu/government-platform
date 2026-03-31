<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementNotificationJob;
use App\Jobs\SendPressReleaseNotificationJob;
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

class AdminController extends Controller
{
    // Maps resource types to their model, fields, and labels
    protected function resourceConfig(): array
    {
        return [
            'services' => [
                'model' => Service::class,
                'label' => 'Services',
                'fields' => [
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'department_id' => ['type' => 'select_model', 'label' => 'Department', 'model' => Department::class, 'display' => 'name', 'nullable' => true],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'cta' => ['type' => 'text', 'label' => 'CTA Text'],
                    'color' => ['type' => 'select', 'label' => 'Color', 'options' => ['primary', 'secondary', 'accent', 'neutral']],
                    'url' => ['type' => 'text', 'label' => 'URL'],
                    'page' => ['type' => 'select', 'label' => 'Page', 'options' => ['landing', 'services']],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'announcements' => [
                'model' => Announcement::class,
                'label' => 'Announcements',
                'fields' => [
                    'category' => ['type' => 'text', 'label' => 'Category'],
                    'category_color' => ['type' => 'select', 'label' => 'Category Color', 'options' => ['primary', 'secondary', 'accent']],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'excerpt' => ['type' => 'textarea', 'label' => 'Excerpt'],
                    'date' => ['type' => 'text', 'label' => 'Date'],
                    'image' => ['type' => 'image', 'label' => 'Image'],
                    'image_alt' => ['type' => 'text', 'label' => 'Image Alt Text'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'press-releases' => [
                'model' => PressRelease::class,
                'label' => 'Press Releases',
                'fields' => [
                    'source' => ['type' => 'text', 'label' => 'Source'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'url' => ['type' => 'text', 'label' => 'URL'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'agencies' => [
                'model' => Agency::class,
                'label' => 'Agencies',
                'fields' => [
                    'agency_group_id' => ['type' => 'select_model', 'label' => 'Agency Group', 'model' => AgencyGroup::class, 'display' => 'category'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'acronym' => ['type' => 'text', 'label' => 'Acronym'],
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'url' => ['type' => 'text', 'label' => 'URL'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'agency-groups' => [
                'model' => AgencyGroup::class,
                'label' => 'Agency Groups',
                'fields' => [
                    'category' => ['type' => 'text', 'label' => 'Category Name'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'leaders' => [
                'model' => Leader::class,
                'label' => 'Leaders',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'position' => ['type' => 'text', 'label' => 'Position'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'departments' => [
                'model' => Department::class,
                'label' => 'Departments',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'acronym' => ['type' => 'text', 'label' => 'Acronym'],
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'chambers' => [
                'model' => Chamber::class,
                'label' => 'Chambers',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'leader' => ['type' => 'text', 'label' => 'Leader'],
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'members' => ['type' => 'number', 'label' => 'Number of Members'],
                    'location' => ['type' => 'text', 'label' => 'Location'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'recent-laws' => [
                'model' => RecentLaw::class,
                'label' => 'Recent Laws',
                'fields' => [
                    'number' => ['type' => 'text', 'label' => 'Law Number'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['Enacted', 'Pending', 'Vetoed']],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'courts' => [
                'model' => Court::class,
                'label' => 'Courts',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'head' => ['type' => 'text', 'label' => 'Head/Chief Justice', 'nullable' => true],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'judiciary-functions' => [
                'model' => JudiciaryFunction::class,
                'label' => 'Judiciary Functions',
                'fields' => [
                    'icon' => ['type' => 'icon_select', 'label' => 'Icon'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'hero' => [
                'model' => HeroSetting::class,
                'label' => 'Hero Settings',
                'fields' => [
                    'badge' => ['type' => 'text', 'label' => 'Badge Text'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'highlight' => ['type' => 'text', 'label' => 'Highlight Text'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'image' => ['type' => 'image', 'label' => 'Image'],
                ],
            ],
        ];
    }

    protected function getConfig(string $type): array
    {
        $config = $this->resourceConfig();
        abort_unless(isset($config[$type]), 404);
        return $config[$type];
    }

    public function dashboard()
    {
        $stats = [
            'Services' => Service::count(),
            'Announcements' => Announcement::count(),
            'Press Releases' => PressRelease::count(),
            'Agencies' => Agency::count(),
            'Leaders' => Leader::count(),
            'Departments' => Department::count(),
            'Chambers' => Chamber::count(),
            'Recent Laws' => RecentLaw::count(),
            'Courts' => Court::count(),
            'Judiciary Functions' => JudiciaryFunction::count(),
        ];
        $resources = $this->resourceConfig();

        return view('admin.dashboard', compact('stats', 'resources'));
    }

    public function index(string $type)
    {
        $config = $this->getConfig($type);
        $query = $config['model']::query();
        if (array_key_exists('sort_order', $config['fields'])) {
            $query->orderBy('sort_order');
        }
        $items = $query->get();

        return view('admin.index', compact('type', 'config', 'items'));
    }

    public function create(string $type)
    {
        $config = $this->getConfig($type);
        $item = null;

        return view('admin.form', compact('type', 'config', 'item'));
    }

    public function store(Request $request, string $type)
    {
        $config = $this->getConfig($type);
        $data = $this->validateFields($request, $config['fields']);
        $data = $this->handleImageUploads($request, $config['fields'], $data);
        $item = $config['model']::create($data);

        // Dispatch notification jobs for specific resources
        if ($type === 'press-releases' && $item instanceof PressRelease) {
            SendPressReleaseNotificationJob::dispatch($item);
        } elseif ($type === 'announcements' && $item instanceof Announcement) {
            SendAnnouncementNotificationJob::dispatch($item);
        }

        return redirect()->route('admin.resource.index', $type)->with('success', "{$config['label']} item created.");
    }

    public function edit(string $type, int $id)
    {
        $config = $this->getConfig($type);
        $item = $config['model']::findOrFail($id);

        return view('admin.form', compact('type', 'config', 'item'));
    }

    public function update(Request $request, string $type, int $id)
    {
        $config = $this->getConfig($type);
        $item = $config['model']::findOrFail($id);
        $data = $this->validateFields($request, $config['fields']);
        $data = $this->handleImageUploads($request, $config['fields'], $data);
        $item->update($data);

        return redirect()->route('admin.resource.index', $type)->with('success', "{$config['label']} item updated.");
    }

    public function destroy(string $type, int $id)
    {
        $config = $this->getConfig($type);
        $config['model']::findOrFail($id)->delete();

        return redirect()->route('admin.resource.index', $type)->with('success', "{$config['label']} item deleted.");
    }

    protected function validateFields(Request $request, array $fields): array
    {
        $rules = [];
        foreach ($fields as $name => $field) {
            $rule = [];
            if ($field['type'] === 'image') {
                // Image fields: file is optional on edit, validated separately
                $rule[] = 'nullable';
                $rule[] = 'image';
                $rule[] = 'max:5120';
                $rules[$name] = $rule;
                continue;
            }
            if (!empty($field['nullable'])) {
                $rule[] = 'nullable';
            } else {
                $rule[] = 'required';
            }
            if ($field['type'] === 'number') {
                $rule[] = 'integer';
            } elseif ($field['type'] === 'select_model') {
                $rule[] = 'integer';
            } elseif ($field['type'] === 'icon_select') {
                $rule[] = 'string';
                $rule[] = 'max:100';
            } else {
                $rule[] = 'string';
                $rule[] = 'max:2000';
            }
            $rules[$name] = $rule;
        }

        return $request->validate($rules);
    }

    protected function handleImageUploads(Request $request, array $fields, array $data): array
    {
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'image' && $request->hasFile($name)) {
                $file = $request->file($name);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/img/uploads'), $filename);
                $data[$name] = '/assets/img/uploads/' . $filename;
            } else if ($field['type'] === 'image' && !$request->hasFile($name)) {
                unset($data[$name]);
            }
        }

        return $data;
    }
}
