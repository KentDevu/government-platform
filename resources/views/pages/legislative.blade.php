@extends('layouts.app')

@section('title', 'Legislative Branch | GOV.PH')

@section('content')

{{-- Page Header --}}
<section class="relative bg-fixed bg-cover bg-center py-32" style="background-image: url('{{ $heroImage }}');">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/40"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4">
                <span class="material-symbols-outlined text-sm">balance</span> Legislative Branch
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ $pageTitle }}</h2>
            <p class="text-lg text-white/80 leading-relaxed">{{ $pageDescription }}</p>
        </div>
    </div>
</section>

{{-- Chambers --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
            @foreach ($chambers as $chamber)
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100">
                    <div class="size-16 rounded-xl bg-gov-primary/10 text-gov-primary flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-3xl">{{ $chamber['icon'] }}</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ $chamber['name'] }}</h3>
                    <p class="text-gov-primary font-semibold text-sm mb-4">{{ $chamber['leader'] }}</p>
                    <p class="text-slate-500 text-sm mb-6">{{ $chamber['description'] }}</p>
                    <div class="flex items-center gap-6 text-sm text-slate-600">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-gov-primary">group</span>
                            {{ $chamber['members'] }} Members
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-gov-primary">location_city</span>
                            {{ $chamber['location'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recent Legislation --}}
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-2 bg-gov-secondary rounded-full"></div>
            <h3 class="text-2xl font-extrabold tracking-tight">Recent Legislation</h3>
        </div>
        <div class="space-y-4">
            @foreach ($recentLaws as $law)
                <div class="flex flex-col md:flex-row md:items-center justify-between bg-slate-50 p-6 rounded-xl border border-slate-100 hover:border-gov-primary/50 transition-all gap-4">
                    <div>
                        <span class="text-xs font-bold text-gov-primary uppercase">{{ $law['number'] }}</span>
                        <h4 class="font-bold mt-1">{{ $law['title'] }}</h4>
                        <p class="text-slate-500 text-sm mt-1">{{ $law['description'] }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shrink-0
                        {{ $law['status'] === 'Enacted' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $law['status'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
