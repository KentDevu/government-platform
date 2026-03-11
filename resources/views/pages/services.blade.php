@extends('layouts.app')

@section('title', 'Services | GOV.PH')

@section('content')

{{-- Page Header --}}
<section class="relative bg-fixed bg-cover bg-center py-32" style="background-image: url('{{ $heroImage }}');">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/40"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4">
                <span class="material-symbols-outlined text-sm">grid_view</span> Government Services
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ $pageTitle }}</h2>
            <p class="text-lg text-white/80 leading-relaxed">{{ $pageDescription }}</p>
        </div>
    </div>
</section>

{{-- Services Grid --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($services as $service)
                <div class="group bg-slate-50 p-8 rounded-2xl border border-slate-100 hover:border-gov-primary/50 hover:shadow-xl transition-all">
                    <div class="size-16 rounded-xl bg-gov-primary/10 text-gov-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-3xl">{{ $service->icon }}</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2">{{ $service->title }}</h4>
                    <p class="text-slate-500 text-sm mb-2">{{ $service->department->name ?? 'N/A' }}</p>
                    <p class="text-slate-600 text-sm mb-6">{{ $service->description }}</p>
                    <a href="{{ $service->url }}" class="inline-flex items-center text-gov-primary text-sm font-bold gap-1 group-hover:gap-2 transition-all">
                        {{ $service->cta }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
