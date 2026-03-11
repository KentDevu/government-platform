@extends('layouts.app')

@section('title', 'Judiciary Branch | GOV.PH')

@section('content')

{{-- Page Header --}}
<section class="relative bg-fixed bg-cover bg-center py-32" style="background-image: url('{{ $heroImage }}');">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/40"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4">
                <span class="material-symbols-outlined text-sm">assured_workload</span> Judiciary Branch
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ $pageTitle }}</h2>
            <p class="text-lg text-white/80 leading-relaxed">{{ $pageDescription }}</p>
        </div>
    </div>
</section>

{{-- Courts --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-2 bg-gov-primary rounded-full"></div>
            <h3 class="text-2xl font-extrabold tracking-tight">Court System</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @foreach ($courts as $court)
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:border-gov-primary/50 hover:shadow-lg transition-all">
                    <div class="size-14 rounded-xl bg-gov-primary/10 text-gov-primary flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-3xl">{{ $court['icon'] }}</span>
                    </div>
                    <h4 class="text-lg font-bold mb-2">{{ $court['name'] }}</h4>
                    <p class="text-slate-500 text-sm mb-4">{{ $court['description'] }}</p>
                    @if (!empty($court['head']))
                        <div class="pt-4 border-t border-slate-200">
                            <p class="text-xs text-slate-400 uppercase font-bold">Head</p>
                            <p class="text-sm font-semibold mt-1">{{ $court['head'] }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Key Functions --}}
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-2 bg-gov-secondary rounded-full"></div>
            <h3 class="text-2xl font-extrabold tracking-tight">Key Functions</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($functions as $function)
                <div class="flex items-start gap-4 bg-slate-50 p-6 rounded-xl border border-slate-100">
                    <div class="size-10 shrink-0 rounded-lg bg-gov-secondary/10 text-gov-secondary flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $function['icon'] }}</span>
                    </div>
                    <div>
                        <h4 class="font-bold mb-1">{{ $function['title'] }}</h4>
                        <p class="text-slate-500 text-sm">{{ $function['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
