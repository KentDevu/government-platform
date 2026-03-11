@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">Overview of all managed content</p>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-10">
    @php
        $icons = [
            'Services' => 'home_repair_service',
            'Announcements' => 'campaign',
            'Press Releases' => 'newspaper',
            'Agencies' => 'account_balance',
            'Leaders' => 'person',
            'Departments' => 'domain',
            'Chambers' => 'groups',
            'Recent Laws' => 'gavel',
            'Courts' => 'assured_workload',
            'Judiciary Functions' => 'balance',
        ];
        $colors = [
            'Services' => 'blue',
            'Announcements' => 'amber',
            'Press Releases' => 'purple',
            'Agencies' => 'emerald',
            'Leaders' => 'rose',
            'Departments' => 'sky',
            'Chambers' => 'indigo',
            'Recent Laws' => 'orange',
            'Courts' => 'teal',
            'Judiciary Functions' => 'violet',
        ];
    @endphp
    @foreach ($stats as $label => $count)
        @php $color = $colors[$label] ?? 'blue'; $icon = $icons[$label] ?? 'data_usage'; @endphp
        <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-lg bg-{{ $color }}-50 text-{{ $color }}-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $count }}</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $label }}</div>
        </div>
    @endforeach
</div>

{{-- Quick Access --}}
<div>
    <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Access</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($resources as $slug => $config)
            <a href="{{ route('admin.resource.index', $slug) }}" class="flex items-center gap-3 p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-blue-50 group-hover:text-blue-600 transition flex-shrink-0">
                    <span class="material-symbols-outlined text-xl">edit_note</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ $config['label'] }}</div>
                    <div class="text-xs text-gray-400">Manage {{ strtolower($config['label']) }}</div>
                </div>
                <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-400 text-lg transition">chevron_right</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
