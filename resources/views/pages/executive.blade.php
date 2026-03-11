@extends('layouts.app')

@section('title', 'Executive Branch | GOV.PH')

@section('content')

{{-- Page Header --}}
<section class="relative bg-fixed bg-cover bg-center py-32" style="background-image: url('{{ $heroImage }}');">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/40"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4">
                <span class="material-symbols-outlined text-sm">gavel</span> Executive Branch
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ $pageTitle }}</h2>
            <p class="text-lg text-white/80 leading-relaxed">{{ $pageDescription }}</p>
        </div>
    </div>
</section>

{{-- Leadership --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-2 bg-gov-secondary rounded-full"></div>
            <h3 class="text-2xl font-extrabold tracking-tight">National Leadership</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            @foreach ($leaders as $leader)
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 text-center">
                    <div class="size-32 mx-auto rounded-full bg-gov-primary/10 text-gov-primary flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-6xl">person</span>
                    </div>
                    <h4 class="text-2xl font-bold mb-1">{{ $leader['name'] }}</h4>
                    <p class="text-gov-primary font-semibold mb-4">{{ $leader['position'] }}</p>
                    <p class="text-slate-500 text-sm max-w-md mx-auto">{{ $leader['description'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Departments --}}
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-2 bg-gov-primary rounded-full"></div>
            <h3 class="text-2xl font-extrabold tracking-tight">Executive Departments</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($departments as $dept)
                <div class="flex items-center gap-4 bg-slate-50 p-5 rounded-xl border border-slate-100 hover:border-gov-primary/50 hover:shadow-lg transition-all">
                    <div class="size-10 shrink-0 rounded-lg bg-gov-primary/10 text-gov-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $dept['icon'] }}</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">{{ $dept['name'] }}</h4>
                        <p class="text-slate-400 text-xs">{{ $dept['acronym'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
