<?php

/**
 * Admin Resource CRUD – Feature Test Suite
 *
 * Tests the full Create → Read → Update → Delete lifecycle for ALL 12 admin
 * resource types, plus RBAC authorization checks for staff roles.
 *
 * Pest concepts demonstrated here:
 *  • uses(RefreshDatabase::class)  – attaches a trait to every test in this file.
 *  • beforeEach() / afterEach()    – lifecycle hooks that run around each test;
 *                                    used here to isolate ADMIN_IPS and public path.
 *  • dataset('name', [...])        – defines reusable argument sets; each entry
 *                                    causes the ->with() test to run once per entry.
 *  • describe('label', fn)         – groups related tests under a heading.
 *  • it('description', fn)->with() – data-driven test; receives dataset args.
 *  • expect($value)->not->toBeNull() – Pest's fluent assertion chain.
 *  • Queue::fake() / Queue::assertPushed() – Laravel fakes for queue jobs.
 */

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
use App\Models\Permission;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase as LaravelTestCase;

// uses() attaches the RefreshDatabase trait so every test gets a clean DB.
uses(RefreshDatabase::class);

// Shared mutable state for environment isolation across beforeEach/afterEach.
$adminResourceCrudState = [
    'previous_admin_ips' => null,
    'previous_public_path' => null,
    'previous_remote_addr' => null,
    'test_public_path' => null,
];

// beforeEach() runs BEFORE every test — sets up a whitelisted IP and temp public dir.
beforeEach(function () use (&$adminResourceCrudState): void {
    $adminResourceCrudState['previous_admin_ips'] = getenv('ADMIN_IPS');
    $adminResourceCrudState['previous_public_path'] = app()->publicPath();
    $adminResourceCrudState['previous_remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? null;

    putenv('ADMIN_IPS=127.0.0.1,::1');
    $_ENV['ADMIN_IPS'] = '127.0.0.1,::1';
    $_SERVER['ADMIN_IPS'] = '127.0.0.1,::1';

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);

    $adminResourceCrudState['test_public_path'] = storage_path(
        'framework/testing/public/AdminResourceCrudPestTest-' . (string) getmypid() . '-' . uniqid('', true)
    );

    File::ensureDirectoryExists($adminResourceCrudState['test_public_path']);
    app()->usePublicPath($adminResourceCrudState['test_public_path']);
    File::ensureDirectoryExists(public_path('assets/img/uploads'));
});

// afterEach() runs AFTER every test — restores original env vars and cleans temp dirs.
afterEach(function () use (&$adminResourceCrudState): void {
    $previousAdminIps = $adminResourceCrudState['previous_admin_ips'];
    $previousPublicPath = $adminResourceCrudState['previous_public_path'];

    if ($previousAdminIps === false || $previousAdminIps === null) {
        putenv('ADMIN_IPS');
        unset($_ENV['ADMIN_IPS'], $_SERVER['ADMIN_IPS']);
    } else {
        putenv('ADMIN_IPS=' . $previousAdminIps);
        $_ENV['ADMIN_IPS'] = $previousAdminIps;
        $_SERVER['ADMIN_IPS'] = $previousAdminIps;
    }

    if (is_string($adminResourceCrudState['previous_remote_addr'])) {
        $_SERVER['REMOTE_ADDR'] = $adminResourceCrudState['previous_remote_addr'];
    } else {
        unset($_SERVER['REMOTE_ADDR']);
    }

    if (is_string($previousPublicPath) && $previousPublicPath !== '') {
        app()->usePublicPath($previousPublicPath);
    }

    if (is_string($adminResourceCrudState['test_public_path'])) {
        File::deleteDirectory($adminResourceCrudState['test_public_path']);
    }
});

/**
 * dataset() — defines the 12 admin resource types as [slug, Model::class, lookupField].
 * Each entry makes the ->with('admin_resource_crud') test run once with those args.
 */
dataset('admin_resource_crud', [
    'services' => ['services', Service::class, 'title'],
    'announcements' => ['announcements', Announcement::class, 'title'],
    'press-releases' => ['press-releases', PressRelease::class, 'title'],
    'agencies' => ['agencies', Agency::class, 'name'],
    'agency-groups' => ['agency-groups', AgencyGroup::class, 'category'],
    'leaders' => ['leaders', Leader::class, 'name'],
    'departments' => ['departments', Department::class, 'name'],
    'chambers' => ['chambers', Chamber::class, 'name'],
    'recent-laws' => ['recent-laws', RecentLaw::class, 'number'],
    'courts' => ['courts', Court::class, 'name'],
    'judiciary-functions' => ['judiciary-functions', JudiciaryFunction::class, 'title'],
    'hero' => ['hero', HeroSetting::class, 'title'],
]);

// describe() groups related tests — these all test the admin CRUD lifecycle.
describe('admin resource crud', function (): void {
    /**
     * Data-driven test: runs once per dataset entry (12 times total).
     * For each resource type it verifies:
     *   1. GET  /create form returns 200
     *   2. POST /store  creates the record + dispatches queue job (announcements/press-releases)
     *   3. GET  /index  and /edit return 200
     *   4. PUT  /update changes the record
     *   5. DELETE /destroy removes the record
     */
    it('allows an admin to perform create read update and delete for each resource', function (
        string $type,
        string $modelClass,
        string $lookupField
    ): void {
        Queue::fake();
        $admin = createContentManagerUser($this);

        [$createPayload, $updatePayload, $createLookupValue, $updateLookupValue] = payloadsForAdminResource($type);

        // Read create form
        $this->actingAs($admin)
            ->get(route('admin.resource.create', ['type' => $type]))
            ->assertOk();

        // Create
        $this->actingAs($admin)
            ->post(route('admin.resource.store', ['type' => $type]), $createPayload)
            ->assertRedirect(route('admin.resource.index', ['type' => $type]));

        if ($type === 'announcements') {
            Queue::assertPushed(SendAnnouncementNotificationJob::class);
        }

        if ($type === 'press-releases') {
            Queue::assertPushed(SendPressReleaseNotificationJob::class);
        }

        $createdItem = $modelClass::query()
            ->where($lookupField, $createLookupValue)
            ->latest('id')
            ->first();

        expect($createdItem)->not->toBeNull();

        $this->assertDatabaseHas($createdItem->getTable(), [
            'id' => $createdItem->id,
            $lookupField => $createLookupValue,
        ]);

        // Read index + edit
        $this->actingAs($admin)
            ->get(route('admin.resource.index', ['type' => $type]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.resource.edit', ['type' => $type, 'id' => $createdItem->id]))
            ->assertOk();

        // Update
        $this->actingAs($admin)
            ->put(route('admin.resource.update', ['type' => $type, 'id' => $createdItem->id]), $updatePayload)
            ->assertRedirect(route('admin.resource.index', ['type' => $type]));

        $this->assertDatabaseHas($createdItem->getTable(), [
            'id' => $createdItem->id,
            $lookupField => $updateLookupValue,
        ]);

        // Delete
        $this->actingAs($admin)
            ->delete(route('admin.resource.destroy', ['type' => $type, 'id' => $createdItem->id]))
            ->assertRedirect(route('admin.resource.index', ['type' => $type]));

        $this->assertDatabaseMissing($createdItem->getTable(), [
            'id' => $createdItem->id,
        ]);
    })->with('admin_resource_crud'); // ->with() feeds dataset entries into the closure args above.

    // Authorization test: staff WITHOUT content.manage permission should be blocked.
    it('forbids staff without content permission from accessing resource routes', function (): void {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create(['is_admin' => false]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $this->actingAs($staff)
            ->get(route('admin.resource.index', ['type' => 'services']))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.resource.create', ['type' => 'services']))
            ->assertForbidden();
    });

    // Authorization test: staff WITH content.manage permission should be allowed.
    it('allows staff with content.manage permission to access resource routes', function (): void {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $permission = Permission::where('name', 'content.manage')->firstOrFail();

        $staff = User::factory()->create(['is_admin' => false]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        $staff->permissions()->syncWithoutDetaching([$permission->id]);

        $this->actingAs($staff)
            ->get(route('admin.resource.index', ['type' => 'services']))
            ->assertOk();
    });
});

function createContentManagerUser(LaravelTestCase $test): User
{
    $test->seed(RbacSeeder::class);

    $adminRole = Role::where('name', 'admin')->firstOrFail();
    $admin = User::factory()->create(['is_admin' => false]);
    $admin->roles()->syncWithoutDetaching([$adminRole->id]);

    return $admin;
}

/**
 * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: string, 3: string}
 */
function payloadsForAdminResource(string $type): array
{
    return match ($type) {
        'services' => servicePayloads(),
        'announcements' => announcementPayloads(),
        'press-releases' => pressReleasePayloads(),
        'agencies' => agencyPayloads(),
        'agency-groups' => agencyGroupPayloads(),
        'leaders' => leaderPayloads(),
        'departments' => departmentPayloads(),
        'chambers' => chamberPayloads(),
        'recent-laws' => recentLawPayloads(),
        'courts' => courtPayloads(),
        'judiciary-functions' => judiciaryFunctionPayloads(),
        'hero' => heroPayloads(),
        default => throw new InvalidArgumentException("Unsupported resource type [{$type}]."),
    };
}

function servicePayloads(): array
{
    $departmentA = Department::create([
        'name' => 'Department of Public Services',
        'acronym' => 'DPS',
        'icon' => 'ri-building-2-line',
        'sort_order' => 1,
    ]);

    $departmentB = Department::create([
        'name' => 'Department of Citizen Affairs',
        'acronym' => 'DCA',
        'icon' => 'ri-government-line',
        'sort_order' => 2,
    ]);

    $createTitle = 'Citizen Support Portal';
    $updateTitle = 'Citizen Support Portal Updated';

    return [
        [
            'icon' => 'ri-government-line',
            'title' => $createTitle,
            'department_id' => $departmentA->id,
            'description' => 'Public service information and citizen support entry point.',
            'cta' => 'Open Portal',
            'color' => 'primary',
            'url' => '/services/citizen-support',
            'page' => 'services',
            'sort_order' => 10,
        ],
        [
            'icon' => 'ri-community-line',
            'title' => $updateTitle,
            'department_id' => $departmentB->id,
            'description' => 'Updated portal for service requests and tracking.',
            'cta' => 'Start Request',
            'color' => 'secondary',
            'url' => '/services/citizen-support-updated',
            'page' => 'landing',
            'sort_order' => 20,
        ],
        $createTitle,
        $updateTitle,
    ];
}

function announcementPayloads(): array
{
    $createTitle = 'City Hall Advisory Bulletin';
    $updateTitle = 'City Hall Advisory Bulletin Updated';

    return [
        [
            'category' => 'Advisory',
            'category_color' => 'primary',
            'title' => $createTitle,
            'excerpt' => 'Initial advisory content for citizens.',
            'date' => '2026-04-06',
            'image_alt' => 'City hall advisory cover image',
            'sort_order' => 3,
        ],
        [
            'category' => 'Public Notice',
            'category_color' => 'secondary',
            'title' => $updateTitle,
            'excerpt' => 'Updated advisory content for citizens.',
            'date' => '2026-04-07',
            'image_alt' => 'Updated advisory cover image',
            'sort_order' => 4,
        ],
        $createTitle,
        $updateTitle,
    ];
}

function pressReleasePayloads(): array
{
    $createTitle = 'Government Program Launch';
    $updateTitle = 'Government Program Launch Update';

    return [
        [
            'source' => 'Office of Public Affairs',
            'title' => $createTitle,
            'url' => 'https://example.test/press/program-launch',
            'sort_order' => 5,
        ],
        [
            'source' => 'Office of Public Affairs',
            'title' => $updateTitle,
            'url' => 'https://example.test/press/program-launch-updated',
            'sort_order' => 6,
        ],
        $createTitle,
        $updateTitle,
    ];
}

function agencyPayloads(): array
{
    $groupA = AgencyGroup::create([
        'category' => 'Executive Offices Group A',
        'sort_order' => 1,
    ]);

    $groupB = AgencyGroup::create([
        'category' => 'Executive Offices Group B',
        'sort_order' => 2,
    ]);

    $createName = 'Department of Citizen Support';
    $updateName = 'Department of Citizen Support Updated';

    return [
        [
            'agency_group_id' => $groupA->id,
            'name' => $createName,
            'acronym' => 'DCS',
            'icon' => 'ri-government-line',
            'url' => 'https://example.test/agencies/dcs',
            'sort_order' => 11,
        ],
        [
            'agency_group_id' => $groupB->id,
            'name' => $updateName,
            'acronym' => 'DCSU',
            'icon' => 'ri-building-line',
            'url' => 'https://example.test/agencies/dcs-updated',
            'sort_order' => 12,
        ],
        $createName,
        $updateName,
    ];
}

function agencyGroupPayloads(): array
{
    $createCategory = 'Independent Constitutional Offices';
    $updateCategory = 'Independent Constitutional Offices Updated';

    return [
        [
            'category' => $createCategory,
            'sort_order' => 21,
        ],
        [
            'category' => $updateCategory,
            'sort_order' => 22,
        ],
        $createCategory,
        $updateCategory,
    ];
}

function leaderPayloads(): array
{
    $createName = 'Alex Martin';
    $updateName = 'Alex Martin Jr.';

    return [
        [
            'name' => $createName,
            'position' => 'Chief Administrator',
            'description' => 'Oversees strategic implementation of citizen services.',
            'sort_order' => 31,
        ],
        [
            'name' => $updateName,
            'position' => 'Chief Administrator',
            'description' => 'Leads policy updates and public coordination.',
            'sort_order' => 32,
        ],
        $createName,
        $updateName,
    ];
}

function departmentPayloads(): array
{
    $createName = 'Department of Licensing';
    $updateName = 'Department of Licensing and Permits';

    return [
        [
            'name' => $createName,
            'acronym' => 'DL',
            'icon' => 'ri-briefcase-line',
            'sort_order' => 41,
        ],
        [
            'name' => $updateName,
            'acronym' => 'DLP',
            'icon' => 'ri-file-list-line',
            'sort_order' => 42,
        ],
        $createName,
        $updateName,
    ];
}

function chamberPayloads(): array
{
    $createName = 'Upper Policy Council';
    $updateName = 'Upper Policy Council Updated';

    return [
        [
            'name' => $createName,
            'leader' => 'Taylor Nguyen',
            'icon' => 'ri-team-line',
            'description' => 'Primary chamber handling long-term policy discussions.',
            'members' => 24,
            'location' => 'Central Government Complex',
            'sort_order' => 51,
        ],
        [
            'name' => $updateName,
            'leader' => 'Jordan Lee',
            'icon' => 'ri-group-line',
            'description' => 'Updated chamber profile and responsibilities.',
            'members' => 26,
            'location' => 'Updated Complex',
            'sort_order' => 52,
        ],
        $createName,
        $updateName,
    ];
}

function recentLawPayloads(): array
{
    $createNumber = 'RA-2026-1001';
    $updateNumber = 'RA-2026-1002';

    return [
        [
            'number' => $createNumber,
            'title' => 'Digital Governance Modernization Act',
            'description' => 'Initial publication of modernization act details.',
            'status' => 'Enacted',
            'sort_order' => 61,
        ],
        [
            'number' => $updateNumber,
            'title' => 'Digital Governance Modernization Act Amendment',
            'description' => 'Updated publication with amendment details.',
            'status' => 'Pending',
            'sort_order' => 62,
        ],
        $createNumber,
        $updateNumber,
    ];
}

function courtPayloads(): array
{
    $createName = 'Regional Administrative Court';
    $updateName = 'Regional Administrative Court Updated';

    return [
        [
            'name' => $createName,
            'icon' => 'ri-scales-line',
            'description' => 'Handles administrative and regulatory disputes.',
            'head' => 'Chief Justice Morgan',
            'sort_order' => 71,
        ],
        [
            'name' => $updateName,
            'icon' => 'ri-scales-3-line',
            'description' => 'Updated jurisdiction and functions.',
            'head' => 'Chief Justice Morgan Jr.',
            'sort_order' => 72,
        ],
        $createName,
        $updateName,
    ];
}

function judiciaryFunctionPayloads(): array
{
    $createTitle = 'Constitutional Review';
    $updateTitle = 'Constitutional Review and Audit';

    return [
        [
            'icon' => 'ri-file-search-line',
            'title' => $createTitle,
            'description' => 'Reviews constitutional compliance of legal actions.',
            'sort_order' => 81,
        ],
        [
            'icon' => 'ri-search-2-line',
            'title' => $updateTitle,
            'description' => 'Updated review scope for constitutional and legal audit.',
            'sort_order' => 82,
        ],
        $createTitle,
        $updateTitle,
    ];
}

function heroPayloads(): array
{
    $createTitle = 'Transparent Governance Starts Here';
    $updateTitle = 'Transparent Governance Starts Here Updated';

    return [
        [
            'badge' => 'Official Government Portal',
            'title' => $createTitle,
            'highlight' => 'Public Service, Simplified',
            'description' => 'Official information, services, and public updates in one place.',
            'image' => UploadedFile::fake()->image('hero-create.png', 1200, 630),
        ],
        [
            'badge' => 'Official Government Portal Updated',
            'title' => $updateTitle,
            'highlight' => 'Service Delivery, Improved',
            'description' => 'Updated hero section for public information and services.',
        ],
        $createTitle,
        $updateTitle,
    ];
}
