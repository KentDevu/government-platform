<?php

namespace Database\Seeders;

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
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'usesecuvia@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        // Hero settings
        HeroSetting::updateOrCreate(['id' => 1], [
            'badge' => 'Official Government Portal',
            'title' => 'The Gateway to',
            'highlight' => 'Citizen Services',
            'description' => 'Access unified government information, digital services, and national updates in one secure location.',
            'image' => '/assets/img/assets/home.jpg',
        ]);

        // Departments (Executive) — created before services so we can reference them
        $departments = [
            ['name' => 'Department of Agriculture', 'acronym' => 'DA', 'icon' => 'agriculture', 'sort_order' => 1],
            ['name' => 'Department of Budget and Management', 'acronym' => 'DBM', 'icon' => 'calculate', 'sort_order' => 2],
            ['name' => 'Department of Education', 'acronym' => 'DepEd', 'icon' => 'school', 'sort_order' => 3],
            ['name' => 'Department of Energy', 'acronym' => 'DOE', 'icon' => 'bolt', 'sort_order' => 4],
            ['name' => 'Department of Environment and Natural Resources', 'acronym' => 'DENR', 'icon' => 'park', 'sort_order' => 5],
            ['name' => 'Department of Finance', 'acronym' => 'DOF', 'icon' => 'account_balance', 'sort_order' => 6],
            ['name' => 'Department of Foreign Affairs', 'acronym' => 'DFA', 'icon' => 'public', 'sort_order' => 7],
            ['name' => 'Department of Health', 'acronym' => 'DOH', 'icon' => 'local_hospital', 'sort_order' => 8],
            ['name' => 'Department of Human Settlements and Urban Development', 'acronym' => 'DHSUD', 'icon' => 'home_work', 'sort_order' => 9],
            ['name' => 'Department of Information and Communications Technology', 'acronym' => 'DICT', 'icon' => 'wifi', 'sort_order' => 10],
            ['name' => 'Department of the Interior and Local Government', 'acronym' => 'DILG', 'icon' => 'location_city', 'sort_order' => 11],
            ['name' => 'Department of Justice', 'acronym' => 'DOJ', 'icon' => 'gavel', 'sort_order' => 12],
            ['name' => 'Department of Labor and Employment', 'acronym' => 'DOLE', 'icon' => 'work', 'sort_order' => 13],
            ['name' => 'Department of Migrant Workers', 'acronym' => 'DMW', 'icon' => 'flight_takeoff', 'sort_order' => 14],
            ['name' => 'Department of National Defense', 'acronym' => 'DND', 'icon' => 'shield', 'sort_order' => 15],
            ['name' => 'Department of Public Works and Highways', 'acronym' => 'DPWH', 'icon' => 'engineering', 'sort_order' => 16],
            ['name' => 'Department of Science and Technology', 'acronym' => 'DOST', 'icon' => 'science', 'sort_order' => 17],
            ['name' => 'Department of Social Welfare and Development', 'acronym' => 'DSWD', 'icon' => 'diversity_3', 'sort_order' => 18],
            ['name' => 'Department of Tourism', 'acronym' => 'DOT', 'icon' => 'travel_explore', 'sort_order' => 19],
            ['name' => 'Department of Trade and Industry', 'acronym' => 'DTI', 'icon' => 'storefront', 'sort_order' => 20],
            ['name' => 'Department of Transportation', 'acronym' => 'DOTr', 'icon' => 'directions_bus', 'sort_order' => 21],
        ];
        foreach ($departments as $d) {
            Department::create($d);
        }

        // Helper to look up department ID by acronym
        $deptId = fn (string $acronym) => Department::where('acronym', $acronym)->value('id');

        // Landing page services
        $landingServices = [
            ['icon' => 'travel', 'title' => 'Renew Passport', 'description' => 'Department of Foreign Affairs online appointment system.', 'cta' => 'Start Application', 'color' => 'primary', 'url' => '#', 'page' => 'landing', 'sort_order' => 1],
            ['icon' => 'assignment_ind', 'title' => 'NBI Clearance', 'description' => 'Apply for multi-purpose clearance for local or overseas use.', 'cta' => 'Apply Now', 'color' => 'secondary', 'url' => '#', 'page' => 'landing', 'sort_order' => 2],
            ['icon' => 'payments', 'title' => 'Pay Taxes', 'description' => 'BIR e-Filing and payment system for individuals and businesses.', 'cta' => 'e-Payment', 'color' => 'accent', 'url' => '#', 'page' => 'landing', 'sort_order' => 3],
            ['icon' => 'badge', 'title' => 'Verify PhilID', 'description' => 'Validate Philippine Identification System (PhilSys) digital credentials.', 'cta' => 'Verify Now', 'color' => 'neutral', 'url' => '#', 'page' => 'landing', 'sort_order' => 4],
        ];

        // Services page services (department_id references parent department)
        $servicesPage = [
            ['icon' => 'travel', 'title' => 'Passport Services', 'department_id' => $deptId('DFA'), 'description' => 'Apply for new passports, renew existing ones, or schedule appointments at DFA consular offices nationwide.', 'cta' => 'Schedule Appointment', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 1],
            ['icon' => 'assignment_ind', 'title' => 'NBI Clearance', 'department_id' => $deptId('DOJ'), 'description' => 'Apply for multi-purpose NBI clearance for employment, travel, or other legal requirements.', 'cta' => 'Apply Online', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 2],
            ['icon' => 'payments', 'title' => 'Tax Filing & Payment', 'department_id' => $deptId('DOF'), 'description' => 'File income tax returns, pay taxes online, and access BIR e-services for individuals and businesses.', 'cta' => 'e-File Now', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 3],
            ['icon' => 'badge', 'title' => 'PhilSys National ID', 'department_id' => null, 'description' => 'Register for the Philippine Identification System (PhilSys) and verify digital credentials.', 'cta' => 'Register Now', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 4],
            ['icon' => 'local_hospital', 'title' => 'PhilHealth Services', 'department_id' => $deptId('DOH'), 'description' => 'Check membership status, file claims, and access universal healthcare benefits online.', 'cta' => 'Check Status', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 5],
            ['icon' => 'school', 'title' => 'SSS Online', 'department_id' => null, 'description' => 'View contributions, apply for loans, file claims, and manage your SSS membership digitally.', 'cta' => 'Access SSS', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 6],
            ['icon' => 'home_work', 'title' => 'Pag-IBIG Fund', 'department_id' => null, 'description' => 'Apply for housing loans, check savings, and manage your Pag-IBIG membership and contributions.', 'cta' => 'View Account', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 7],
            ['icon' => 'directions_car', 'title' => 'LTO Services', 'department_id' => $deptId('DOTr'), 'description' => "Renew driver's licenses, register vehicles, and access LTO online appointment systems.", 'cta' => 'Book Appointment', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 8],
            ['icon' => 'description', 'title' => 'Civil Registry', 'department_id' => null, 'description' => 'Request birth certificates, marriage certificates, and other civil registry documents online.', 'cta' => 'Request Document', 'color' => 'primary', 'url' => '#', 'page' => 'services', 'sort_order' => 9],
        ];

        foreach (array_merge($landingServices, $servicesPage) as $s) {
            Service::create($s);
        }

        // Announcements
        $announcements = [
            ['category' => 'Economic Update', 'category_color' => 'primary', 'title' => 'Digital Transformation Roadmap 2024 Launched to Modernize Public Services', 'excerpt' => 'The government unveils a comprehensive strategy to digitize all key citizen transactions, aiming for a paperless and more efficient bureaucracy by year-end.', 'date' => 'Oct 24, 2023', 'image' => '/assets/img/news-digital.svg', 'image_alt' => 'Digital transformation roadmap illustration with data charts', 'sort_order' => 1],
            ['category' => 'Sustainability', 'category_color' => 'secondary', 'title' => 'National Green Energy Initiative Exceeds Q3 Targets', 'excerpt' => 'Renewable energy projects across the archipelago see record adoption rates, reinforcing the country\'s commitment to climate action and energy security.', 'date' => 'Oct 22, 2023', 'image' => '/assets/img/news-green-energy.svg', 'image_alt' => 'Green energy initiative with solar panels and wind turbines', 'sort_order' => 2],
        ];
        foreach ($announcements as $a) {
            Announcement::create($a);
        }

        // Press Releases
        $presses = [
            ['source' => 'Executive Office', 'title' => 'President issues Executive Order on streamlining trade permits', 'url' => '#', 'sort_order' => 1],
            ['source' => 'Dept. of Finance', 'title' => 'PH maintains strong credit rating despite global headwinds', 'url' => '#', 'sort_order' => 2],
            ['source' => 'DOH', 'title' => 'National Vaccination Days scheduled for next month', 'url' => '#', 'sort_order' => 3],
            ['source' => 'PCOO', 'title' => 'Freedom of Information (FOI) portal updates released', 'url' => '#', 'sort_order' => 4],
        ];
        foreach ($presses as $p) {
            PressRelease::create($p);
        }

        // Agency Groups & Agencies
        $agencyGroups = [
            [
                'category' => 'Constitutional Offices',
                'sort_order' => 1,
                'agencies' => [
                    ['name' => 'Office of the President', 'acronym' => 'OP', 'icon' => 'stars', 'url' => '#', 'sort_order' => 1],
                    ['name' => 'Office of the Vice President', 'acronym' => 'OVP', 'icon' => 'star_half', 'url' => '#', 'sort_order' => 2],
                    ['name' => 'Commission on Audit', 'acronym' => 'COA', 'icon' => 'fact_check', 'url' => '#', 'sort_order' => 3],
                    ['name' => 'Commission on Elections', 'acronym' => 'COMELEC', 'icon' => 'how_to_vote', 'url' => '#', 'sort_order' => 4],
                    ['name' => 'Civil Service Commission', 'acronym' => 'CSC', 'icon' => 'groups', 'url' => '#', 'sort_order' => 5],
                    ['name' => 'Commission on Human Rights', 'acronym' => 'CHR', 'icon' => 'diversity_3', 'url' => '#', 'sort_order' => 6],
                ],
            ],
            [
                'category' => 'Executive Departments',
                'sort_order' => 2,
                'agencies' => [
                    ['name' => 'Department of Education', 'acronym' => 'DepEd', 'icon' => 'school', 'url' => '#', 'sort_order' => 1],
                    ['name' => 'Department of Health', 'acronym' => 'DOH', 'icon' => 'local_hospital', 'url' => '#', 'sort_order' => 2],
                    ['name' => 'Department of Finance', 'acronym' => 'DOF', 'icon' => 'account_balance', 'url' => '#', 'sort_order' => 3],
                    ['name' => 'Department of Foreign Affairs', 'acronym' => 'DFA', 'icon' => 'public', 'url' => '#', 'sort_order' => 4],
                    ['name' => 'Department of National Defense', 'acronym' => 'DND', 'icon' => 'shield', 'url' => '#', 'sort_order' => 5],
                    ['name' => 'Department of the Interior and Local Government', 'acronym' => 'DILG', 'icon' => 'location_city', 'url' => '#', 'sort_order' => 6],
                ],
            ],
            [
                'category' => 'Other Key Agencies',
                'sort_order' => 3,
                'agencies' => [
                    ['name' => 'Bangko Sentral ng Pilipinas', 'acronym' => 'BSP', 'icon' => 'savings', 'url' => '#', 'sort_order' => 1],
                    ['name' => 'Philippine Statistics Authority', 'acronym' => 'PSA', 'icon' => 'bar_chart', 'url' => '#', 'sort_order' => 2],
                    ['name' => 'National Economic and Development Authority', 'acronym' => 'NEDA', 'icon' => 'trending_up', 'url' => '#', 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($agencyGroups as $groupData) {
            $agencies = $groupData['agencies'];
            unset($groupData['agencies']);
            $group = AgencyGroup::create($groupData);
            foreach ($agencies as $agency) {
                $agency['agency_group_id'] = $group->id;
                Agency::create($agency);
            }
        }

        // Leaders (Executive)
        $leaders = [
            ['name' => 'Ferdinand R. Marcos Jr.', 'position' => 'President of the Philippines', 'description' => 'The President is the Head of State, Head of Government, and Commander-in-Chief of all armed forces of the Philippines.', 'sort_order' => 1],
            ['name' => 'Sara Z. Duterte', 'position' => 'Vice President of the Philippines', 'description' => 'The Vice President may be appointed as a member of the Cabinet and shall assume the Presidency in case of a vacancy.', 'sort_order' => 2],
        ];
        foreach ($leaders as $l) {
            Leader::create($l);
        }

        // Departments already created above (before services)

        // Chambers (Legislative)
        $chambers = [
            ['name' => 'Senate of the Philippines', 'leader' => 'Senate President: Juan Miguel Zubiri', 'icon' => 'account_balance', 'description' => 'The upper chamber of Congress, composed of 24 senators elected at-large by qualified voters. Senators serve a term of six years.', 'members' => 24, 'location' => 'Pasay City', 'sort_order' => 1],
            ['name' => 'House of Representatives', 'leader' => 'Speaker: Martin Romualdez', 'icon' => 'groups', 'description' => 'The lower chamber of Congress, consisting of district and party-list representatives. Members serve a three-year term.', 'members' => 316, 'location' => 'Quezon City', 'sort_order' => 2],
        ];
        foreach ($chambers as $c) {
            Chamber::create($c);
        }

        // Recent Laws (Legislative)
        $laws = [
            ['number' => 'RA 12066', 'title' => 'CREATE MORE Act', 'description' => 'Corporate Recovery and Tax Incentives for Enterprises to Maximize Opportunities for Reinvigorating the Economy.', 'status' => 'Enacted', 'sort_order' => 1],
            ['number' => 'RA 12001', 'title' => 'Kabalikat sa Pagtuturo Act', 'description' => 'Providing additional benefits and allowances to public school teachers and teaching-related personnel.', 'status' => 'Enacted', 'sort_order' => 2],
            ['number' => 'SB 2901', 'title' => 'Philippine Maritime Zones Act', 'description' => 'Defining the maritime zones of the Philippines consistent with UNCLOS and the 2016 Arbitral Award.', 'status' => 'Enacted', 'sort_order' => 3],
            ['number' => 'HB 10557', 'title' => 'E-Government Act', 'description' => 'Establishing the framework for electronic governance and digital government transformation.', 'status' => 'Pending', 'sort_order' => 4],
        ];
        foreach ($laws as $l) {
            RecentLaw::create($l);
        }

        // Courts (Judiciary)
        $courts = [
            ['name' => 'Supreme Court', 'icon' => 'assured_workload', 'description' => 'The highest court of the land, composed of a Chief Justice and 14 Associate Justices. It has the power of judicial review.', 'head' => 'Chief Justice Alexander G. Gesmundo', 'sort_order' => 1],
            ['name' => 'Court of Appeals', 'icon' => 'balance', 'description' => 'An appellate court with 69 justices organized into 23 divisions of three members each.', 'head' => 'Presiding Justice Remedios Salazar-Fernando', 'sort_order' => 2],
            ['name' => 'Sandiganbayan', 'icon' => 'policy', 'description' => 'A special appellate court that handles criminal cases involving government officials and employees.', 'head' => 'Presiding Justice Amparo M. Cabotaje-Tang', 'sort_order' => 3],
            ['name' => 'Court of Tax Appeals', 'icon' => 'receipt_long', 'description' => 'A special court exercising exclusive appellate jurisdiction over tax-related cases.', 'head' => 'Presiding Justice Roman G. Del Rosario', 'sort_order' => 4],
            ['name' => 'Regional Trial Courts', 'icon' => 'gavel', 'description' => 'Courts of general jurisdiction organized in each of the judicial regions across the country.', 'head' => '', 'sort_order' => 5],
            ['name' => 'Municipal Trial Courts', 'icon' => 'location_city', 'description' => 'First-level courts handling minor criminal and civil cases within their territorial jurisdiction.', 'head' => '', 'sort_order' => 6],
        ];
        foreach ($courts as $c) {
            Court::create($c);
        }

        // Judiciary Functions
        $functions = [
            ['icon' => 'search', 'title' => 'Judicial Review', 'description' => 'The power to determine the constitutionality of acts of government, particularly laws passed by Congress.', 'sort_order' => 1],
            ['icon' => 'description', 'title' => 'Rule-Making', 'description' => 'The Supreme Court promulgates rules concerning the protection of constitutional rights and procedure in all courts.', 'sort_order' => 2],
            ['icon' => 'supervisor_account', 'title' => 'Administrative Supervision', 'description' => 'The Supreme Court exercises administrative supervision over all courts and court personnel.', 'sort_order' => 3],
            ['icon' => 'how_to_reg', 'title' => 'Bar Admissions', 'description' => 'The Supreme Court has authority over the admission to the practice of law in the Philippines.', 'sort_order' => 4],
        ];
        foreach ($functions as $f) {
            JudiciaryFunction::create($f);
        }
    }
}
