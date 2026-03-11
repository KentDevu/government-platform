@extends('layouts.app')

@section('title', 'Agencies | GOV.PH')

@section('content')

{{-- Page Header --}}
<section class="relative bg-fixed bg-cover bg-center py-32" style="background-image: url('{{ $heroImage }}');">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/40"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4">
                <span class="material-symbols-outlined text-sm">account_balance</span> Government Directory
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ $pageTitle }}</h2>
            <p class="text-lg text-white/80 leading-relaxed">{{ $pageDescription }}</p>
        </div>
    </div>
</section>

{{-- Agencies List --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        @foreach ($agencyGroups as $group)
            <div class="mb-12">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-8 w-2 bg-gov-primary rounded-full"></div>
                    <h3 class="text-2xl font-extrabold tracking-tight">{{ $group['category'] }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($group['agencies'] as $agency)
                        <div class="flex items-start gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100 hover:border-gov-primary/50 hover:shadow-lg transition-all">
                            <div class="size-12 shrink-0 rounded-xl bg-gov-primary/10 text-gov-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl">{{ $agency['icon'] }}</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-base mb-1">{{ $agency['name'] }}</h4>
                                <p class="text-slate-500 text-xs mb-2">{{ $agency['acronym'] }}</p>
                                <a href="{{ $agency['url'] }}" class="text-gov-primary text-xs font-bold hover:underline">Visit Website &rarr;</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection
