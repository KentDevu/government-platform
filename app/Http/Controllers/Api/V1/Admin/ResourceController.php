<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\AgencyGroupResource;
use App\Http\Resources\AgencyResource;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ChamberResource;
use App\Http\Resources\CourtResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\HeroSettingResource;
use App\Http\Resources\JudiciaryFunctionResource;
use App\Http\Resources\LeaderResource;
use App\Http\Resources\PressReleaseResource;
use App\Http\Resources\RecentLawResource;
use App\Http\Resources\ServiceResource;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    /**
     * Resource type → [model class, resource class, label, fields config].
     */
    private function resourceConfig(): array
    {
        return [
            'services' => [
                'model' => Service::class,
                'resource' => ServiceResource::class,
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
                'resource' => AnnouncementResource::class,
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
                'resource' => PressReleaseResource::class,
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
                'resource' => AgencyResource::class,
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
                'resource' => AgencyGroupResource::class,
                'label' => 'Agency Groups',
                'fields' => [
                    'category' => ['type' => 'text', 'label' => 'Category Name'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                ],
            ],
            'leaders' => [
                'model' => Leader::class,
                'resource' => LeaderResource::class,
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
                'resource' => DepartmentResource::class,
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
                'resource' => ChamberResource::class,
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
                'resource' => RecentLawResource::class,
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
                'resource' => CourtResource::class,
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
                'resource' => JudiciaryFunctionResource::class,
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
                'resource' => HeroSettingResource::class,
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

    private function getConfig(string $type): array
    {
        $config = $this->resourceConfig();

        if (!isset($config[$type])) {
            abort(404, "Resource type '{$type}' not found.");
        }

        return $config[$type];
    }

    /**
     * List all items of a resource type.
     */
    public function index(Request $request, string $type): JsonResponse
    {
        $config = $this->getConfig($type);
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = $config['model']::query();

        if (array_key_exists('sort_order', $config['fields'])) {
            $query->orderBy('sort_order');
        }

        $items = $query->paginate($perPage);

        return $this->paginatedResponse(
            $config['resource']::collection($items),
            $items,
        );
    }

    /**
     * Show a single resource item.
     */
    public function show(string $type, int $id): JsonResponse
    {
        $config = $this->getConfig($type);
        $item = $config['model']::findOrFail($id);

        return $this->successResponse(
            new $config['resource']($item),
        );
    }

    /**
     * Create a new resource item.
     */
    public function store(Request $request, string $type): JsonResponse
    {
        $config = $this->getConfig($type);
        $data = $this->validateFields($request, $config['fields']);
        $data = $this->handleImageUploads($request, $config['fields'], $data);

        $item = $config['model']::create($data);

        // Dispatch notification jobs
        if ($type === 'press-releases' && $item instanceof PressRelease) {
            SendPressReleaseNotificationJob::dispatch($item);
        } elseif ($type === 'announcements' && $item instanceof Announcement) {
            SendAnnouncementNotificationJob::dispatch($item);
        }

        return $this->createdResponse(
            new $config['resource']($item),
            "{$config['label']} item created.",
        );
    }

    /**
     * Update an existing resource item.
     */
    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $config = $this->getConfig($type);
        $item = $config['model']::findOrFail($id);
        $data = $this->validateFields($request, $config['fields']);
        $data = $this->handleImageUploads($request, $config['fields'], $data);

        $item->update($data);

        return $this->successResponse(
            new $config['resource']($item->fresh()),
            "{$config['label']} item updated.",
        );
    }

    /**
     * Delete a resource item.
     */
    public function destroy(string $type, int $id): JsonResponse
    {
        $config = $this->getConfig($type);
        $config['model']::findOrFail($id)->delete();

        return $this->successResponse(null, "{$config['label']} item deleted.");
    }

    /**
     * Build validation rules from the fields config and validate.
     */
    private function validateFields(Request $request, array $fields): array
    {
        $rules = [];

        foreach ($fields as $name => $field) {
            $rule = [];

            if ($field['type'] === 'image') {
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
                if (!empty($field['model'])) {
                    $model = new $field['model'];
                    $rule[] = 'exists:' . $model->getTable() . ',id';
                }
            } elseif ($field['type'] === 'select' && !empty($field['options'])) {
                $rule[] = Rule::in($field['options']);
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

    /**
     * Handle image file uploads for resource fields.
     */
    private function handleImageUploads(Request $request, array $fields, array $data): array
    {
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'image' && $request->hasFile($name)) {
                $file = $request->file($name);
                // Use safe random filename to prevent path traversal
                $filename = time() . '_' . Str::random(20) . '.' . $file->guessExtension();
                $file->move(public_path('assets/img/uploads'), $filename);
                $data[$name] = '/assets/img/uploads/' . $filename;
            } elseif ($field['type'] === 'image' && !$request->hasFile($name)) {
                unset($data[$name]);
            }
        }

        return $data;
    }
}
