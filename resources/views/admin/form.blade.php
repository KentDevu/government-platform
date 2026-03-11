@extends('admin.layout')

@section('title', ($item ? 'Edit' : 'Create') . ' ' . $config['label'])

@section('content')
<div class="w-full">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.resource.index', $type) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-600 transition mb-4 group">
            <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
            Back to {{ $config['label'] }}
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $item ? 'Edit' : 'Create New' }} {{ Str::singular($config['label']) }}</h2>
        <p class="text-sm text-gray-400 mt-1">{{ $item ? 'Make changes and save when ready' : 'Fill in the required fields below' }}</p>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <span class="material-symbols-outlined text-red-500 text-lg mt-0.5 flex-shrink-0">error</span>
            <div>
                <p class="text-sm font-medium text-red-700 mb-1">Please fix the following errors:</p>
                <ul class="text-sm text-red-600 space-y-0.5 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form Card --}}
    <form method="POST" action="{{ $item ? route('admin.resource.update', [$type, $item->id]) : route('admin.resource.store', $type) }}" enctype="multipart/form-data">
        @csrf
        @if ($item)
            @method('PUT')
        @endif

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            {{-- Card Header --}}
            <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-lg">edit_note</span>
                    {{ Str::singular($config['label']) }} Details
                </h3>
            </div>

            {{-- Fields --}}
            <div class="px-5 sm:px-6 py-5 space-y-6">
                @foreach ($config['fields'] as $fieldName => $field)
                    <div class="group">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="{{ $fieldName }}">
                            {{ $field['label'] }}
                            @if (empty($field['nullable']))
                                <span class="text-red-400 ml-0.5">*</span>
                            @endif
                        </label>

                        @if ($field['type'] === 'textarea')
                            <textarea
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm resize-y placeholder-gray-400"
                                id="{{ $fieldName }}"
                                name="{{ $fieldName }}"
                                rows="4"
                                placeholder="Enter {{ strtolower($field['label']) }}..."
                            >{{ old($fieldName, $item?->$fieldName) }}</textarea>

                        @elseif ($field['type'] === 'select')
                            <div class="relative">
                                <select
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm appearance-none cursor-pointer pr-10"
                                    id="{{ $fieldName }}"
                                    name="{{ $fieldName }}"
                                >
                                    @foreach ($field['options'] as $option)
                                        <option value="{{ $option }}" {{ old($fieldName, $item?->$fieldName) === $option ? 'selected' : '' }}>
                                            {{ ucfirst($option) }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined text-gray-400 text-lg absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">expand_more</span>
                            </div>

                        @elseif ($field['type'] === 'select_model')
                            @php
                                $relatedItems = $field['model']::orderBy('sort_order')->get();
                            @endphp
                            <div class="relative">
                                <select
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm appearance-none cursor-pointer pr-10"
                                    id="{{ $fieldName }}"
                                    name="{{ $fieldName }}"
                                >
                                    @if (!empty($field['nullable']))
                                        <option value="">— None —</option>
                                    @endif
                                    @foreach ($relatedItems as $related)
                                        <option value="{{ $related->id }}" {{ old($fieldName, $item?->$fieldName) == $related->id ? 'selected' : '' }}>
                                            {{ $related->{$field['display']} }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined text-gray-400 text-lg absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">expand_more</span>
                            </div>

                        @elseif ($field['type'] === 'icon_select')
                            @php
                                $iconList = [
                                    'Government' => ['account_balance', 'assured_workload', 'gavel', 'policy', 'shield', 'admin_panel_settings', 'how_to_reg', 'badge', 'public', 'groups', 'diversity_3', 'supervisor_account', 'person'],
                                    'Services' => ['home_repair_service', 'assignment_ind', 'payments', 'receipt_long', 'calculate', 'description', 'storefront', 'work', 'engineering', 'build', 'handyman', 'support_agent'],
                                    'Education & Health' => ['school', 'local_hospital', 'science', 'biotech', 'health_and_safety', 'medication', 'psychology', 'vaccines', 'medical_services'],
                                    'Transport & Infrastructure' => ['directions_car', 'directions_bus', 'flight_takeoff', 'travel', 'travel_explore', 'location_city', 'domain', 'apartment', 'home_work', 'map'],
                                    'Nature & Agriculture' => ['agriculture', 'park', 'forest', 'eco', 'energy_savings_leaf', 'water_drop', 'landscape', 'pets'],
                                    'Technology & Communication' => ['wifi', 'devices', 'computer', 'language', 'campaign', 'newspaper', 'rss_feed', 'mail', 'call', 'smart_toy'],
                                    'Finance & Business' => ['attach_money', 'savings', 'trending_up', 'analytics', 'monitoring', 'query_stats', 'bar_chart', 'pie_chart'],
                                    'Security & Legal' => ['local_police', 'military_tech', 'security', 'verified_user', 'lock', 'balance', 'family_restroom', 'emergency'],
                                    'General' => ['grid_view', 'category', 'star', 'bolt', 'light_mode', 'dark_mode', 'search', 'info', 'help', 'settings', 'arrow_forward', 'open_in_new', 'check_circle', 'flag', 'pin_drop'],
                                ];
                                $currentIcon = old($fieldName, $item?->$fieldName) ?? '';
                            @endphp
                            <div x-data="{
                                open: false,
                                search: '',
                                selected: '{{ $currentIcon }}',
                                icons: {{ Js::from($iconList) }},
                                get filtered() {
                                    if (!this.search) return this.icons;
                                    const q = this.search.toLowerCase();
                                    let result = {};
                                    for (const [group, icons] of Object.entries(this.icons)) {
                                        const matched = icons.filter(i => i.includes(q));
                                        if (matched.length) result[group] = matched;
                                    }
                                    return result;
                                },
                                select(icon) {
                                    this.selected = icon;
                                    this.open = false;
                                    this.search = '';
                                }
                            }" class="relative">
                                <input type="hidden" name="{{ $fieldName }}" :value="selected" />

                                {{-- Trigger Button --}}
                                <button type="button" @click="open = !open" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm flex items-center gap-3 text-left">
                                    <span x-show="selected" class="material-symbols-outlined text-xl text-blue-600" x-text="selected"></span>
                                    <span x-show="!selected" class="material-symbols-outlined text-xl text-gray-300">add_circle</span>
                                    <span x-text="selected || 'Choose an icon...'" :class="selected ? 'text-gray-800 font-medium' : 'text-gray-400'"></span>
                                    <span class="material-symbols-outlined text-gray-400 text-lg ml-auto pointer-events-none">expand_more</span>
                                </button>

                                {{-- Dropdown Panel --}}
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-80 overflow-hidden flex flex-col">
                                    {{-- Search --}}
                                    <div class="p-3 border-b border-gray-100">
                                        <input x-ref="iconSearch" x-model="search" @keydown.escape="open = false" type="text" placeholder="Search icons..." class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                                    </div>

                                    {{-- Icon Grid --}}
                                    <div class="overflow-y-auto p-3 space-y-3" x-init="$watch('open', v => { if (v) $nextTick(() => $refs.iconSearch.focus()) })">
                                        <template x-for="(icons, group) in filtered" :key="group">
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2 px-1" x-text="group"></p>
                                                <div class="grid grid-cols-6 sm:grid-cols-8 gap-1">
                                                    <template x-for="icon in icons" :key="icon">
                                                        <button type="button" @click="select(icon)"
                                                            :class="selected === icon ? 'bg-blue-100 border-blue-400 text-blue-600' : 'bg-gray-50 border-transparent text-gray-600 hover:bg-blue-50 hover:text-blue-600'"
                                                            class="flex flex-col items-center justify-center p-2 rounded-lg border transition cursor-pointer group/icon"
                                                            :title="icon">
                                                            <span class="material-symbols-outlined text-xl" x-text="icon"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        @elseif ($field['type'] === 'image')
                            <div class="space-y-3">
                                @if ($item && $item->$fieldName)
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <img src="{{ $item->$fieldName }}" alt="Current" class="w-24 h-16 object-cover rounded-md border border-gray-200"/>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-600">Current image</p>
                                            <p class="text-[11px] text-gray-400 truncate">{{ basename($item->$fieldName) }}</p>
                                        </div>
                                    </div>
                                @endif

                                <label for="{{ $fieldName }}" class="block cursor-pointer" id="drop_{{ $fieldName }}">
                                    <div class="w-full border-2 border-dashed border-gray-200 hover:border-blue-400 rounded-lg py-8 px-4 text-center transition-all group bg-gray-50 hover:bg-blue-50/30">
                                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm group-hover:shadow border border-gray-100 group-hover:border-blue-200 transition">
                                            <span class="material-symbols-outlined text-2xl text-gray-300 group-hover:text-blue-500 transition">cloud_upload</span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">
                                            Click to upload{{ $item && $item->$fieldName ? ' a new image' : '' }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP &mdash; Max 5MB</p>
                                    </div>
                                    <input class="hidden" id="{{ $fieldName }}" name="{{ $fieldName }}" type="file" accept="image/*" onchange="previewImage(this, '{{ $fieldName }}')"/>
                                </label>

                                <div id="preview_{{ $fieldName }}" class="hidden p-3 bg-blue-50 rounded-lg border border-blue-200 flex items-center gap-4">
                                    <img id="preview_img_{{ $fieldName }}" src="" alt="Preview" class="w-24 h-16 object-cover rounded-md border border-blue-200"/>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-blue-700">New image selected</p>
                                        <p id="preview_name_{{ $fieldName }}" class="text-[11px] text-blue-500 truncate"></p>
                                    </div>
                                    <button type="button" onclick="clearPreview('{{ $fieldName }}')" class="p-1.5 text-blue-400 hover:text-red-500 hover:bg-white rounded-md transition flex-shrink-0">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </div>

                        @else
                            <input
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm placeholder-gray-400"
                                id="{{ $fieldName }}"
                                name="{{ $fieldName }}"
                                type="{{ $field['type'] === 'number' ? 'number' : 'text' }}"
                                value="{{ old($fieldName, $item?->$fieldName) }}"
                                placeholder="Enter {{ strtolower($field['label']) }}..."
                            />
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Action Bar --}}
            <div class="px-5 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 rounded-b-lg">
                <a href="{{ route('admin.resource.index', $type) }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition text-center">
                    Cancel
                </a>
                <button class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-sm hover:shadow flex items-center justify-center gap-2" type="submit">
                    <span class="material-symbols-outlined text-[18px]">{{ $item ? 'save' : 'add' }}</span>
                    {{ $item ? 'Save Changes' : 'Create' }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function previewImage(input, fieldName) {
    const preview = document.getElementById('preview_' + fieldName);
    const img = document.getElementById('preview_img_' + fieldName);
    const name = document.getElementById('preview_name_' + fieldName);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
        name.textContent = input.files[0].name;
        preview.classList.remove('hidden');
    }
}
function clearPreview(fieldName) {
    document.getElementById(fieldName).value = '';
    document.getElementById('preview_' + fieldName).classList.add('hidden');
}
</script>
@endsection
