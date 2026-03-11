@extends('admin.layout')

@section('title', $config['label'])

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $config['label'] }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }} total</p>
    </div>
    <a href="{{ route('admin.resource.create', $type) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm hover:shadow-md w-full sm:w-auto">
        <span class="material-symbols-outlined text-lg">add</span> Add New
    </a>
</div>

@if ($items->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-3xl text-gray-300">inbox</span>
        </div>
        <p class="text-sm font-medium text-gray-500">No items yet</p>
        <p class="text-xs text-gray-400 mt-1">Create one to get started</p>
        <a href="{{ route('admin.resource.create', $type) }}" class="inline-flex items-center gap-1 mt-4 text-sm text-blue-600 font-medium hover:text-blue-700">
            <span class="material-symbols-outlined text-base">add</span> Create first item
        </a>
    </div>
@else
    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider w-14">#</th>
                        @foreach (array_slice($config['fields'], 0, 4) as $fieldName => $field)
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider">{{ $field['label'] }}</th>
                        @endforeach
                        <th class="text-right px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-4 py-3.5 text-gray-400 font-mono text-xs">{{ $item->id }}</td>
                            @foreach (array_slice($config['fields'], 0, 4) as $fieldName => $field)
                                <td class="px-4 py-3.5 text-gray-700 max-w-[220px]">
                                    @if ($field['type'] === 'image' && $item->$fieldName)
                                        <img src="{{ $item->$fieldName }}" alt="" class="w-12 h-8 object-cover rounded"/>
                                    @elseif ($field['type'] === 'icon_select')
                                        <span class="material-symbols-outlined text-xl text-blue-600">{{ $item->$fieldName }}</span>
                                    @elseif ($field['type'] === 'select_model')
                                        @php
                                            $related = $item->$fieldName ? $field['model']::find($item->$fieldName) : null;
                                        @endphp
                                        <span class="truncate block">{{ $related ? $related->{$field['display']} : '—' }}</span>
                                    @else
                                        <span class="truncate block">{{ Str::limit($item->$fieldName, 50) }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.resource.edit', [$type, $item->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.resource.destroy', [$type, $item->id]) }}" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" type="submit" title="Delete">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @foreach ($items as $item)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0 flex-1">
                        @php $firstField = array_key_first($config['fields']); @endphp
                        <span class="text-[10px] font-bold text-gray-400 uppercase">#{{ $item->id }}</span>
                        @foreach (array_slice($config['fields'], 0, 2) as $fieldName => $field)
                            @if ($field['type'] === 'image' && $item->$fieldName)
                                <img src="{{ $item->$fieldName }}" alt="" class="w-24 h-16 object-cover rounded-lg mt-2"/>
                            @elseif ($field['type'] === 'icon_select')
                                <span class="material-symbols-outlined text-xl text-blue-600">{{ $item->$fieldName }}</span>
                            @elseif ($field['type'] === 'select_model')
                                @php
                                    $related = $item->$fieldName ? $field['model']::find($item->$fieldName) : null;
                                @endphp
                                <p class="text-sm {{ $loop->first ? 'font-semibold text-gray-900' : 'text-gray-500 mt-0.5' }} truncate">{{ $related ? $related->{$field['display']} : '—' }}</p>
                            @else
                                <p class="text-sm {{ $loop->first ? 'font-semibold text-gray-900' : 'text-gray-500 mt-0.5' }} truncate">{{ Str::limit($item->$fieldName, 60) }}</p>
                            @endif
                        @endforeach
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        <a href="{{ route('admin.resource.edit', [$type, $item->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>
                        <form method="POST" action="{{ route('admin.resource.destroy', [$type, $item->id]) }}" onsubmit="return confirm('Delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button class="p-2 text-red-400 hover:bg-red-50 rounded-lg" type="submit">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
                @foreach (array_slice($config['fields'], 2, 2) as $fieldName => $field)
                    @if ($item->$fieldName)
                        <div class="text-xs text-gray-400 mt-1">
                            <span class="font-medium">{{ $field['label'] }}:</span> {{ Str::limit($item->$fieldName, 40) }}
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endif
@endsection
