@extends('layouts.app')

@section('content')

{{-- ==================== HERO SECTION ==================== --}}
<section class="relative h-[600px] w-full flex items-center overflow-hidden bg-fixed bg-cover bg-center" style="background-image: url('{{ $hero['image'] }}');">\
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/40 to-transparent z-10"></div>
    <div class="absolute inset-0 bg-gov-primary/10 mix-blend-overlay z-10"></div>
    <div class="absolute inset-0 z-10 animate-shimmer pointer-events-none"></div>
    <div class="relative z-20 max-w-[1280px] mx-auto px-4 lg:px-10 w-full">
        <div class="max-w-2xl">
            <div class="animate-hero-badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gov-accent/20 border border-gov-accent/30 text-gov-accent text-[11px] font-semibold uppercase tracking-[0.15em] mb-6">
                <span class="material-symbols-outlined text-sm">verified</span> {{ $hero['badge'] }}
            </div>
            <h2 class="animate-hero-slide-up hero-slide-delay-1 font-display text-[clamp(2.5rem,5vw,4.5rem)] font-extrabold text-white leading-[1.08] mb-6 tracking-[-0.025em]">
                {{ $hero['title'] }} <span class="text-gov-accent">{{ $hero['highlight'] }}</span>
            </h2>
            <p class="animate-hero-slide-up hero-slide-delay-2 text-base lg:text-lg text-slate-300 mb-10 leading-[1.7] font-normal max-w-xl">
                {{ $hero['description'] }}
            </p>
            <div class="animate-hero-slide-up hero-slide-delay-3 flex flex-wrap gap-4">
                <a href="{{ route('services') }}" class="btn-glow h-12 px-7 rounded-xl bg-gov-primary text-white font-semibold text-[15px] flex items-center gap-2 tracking-wide">
                    Access Services <span class="material-symbols-outlined text-xl">arrow_forward</span>
                </a>
                <a href="{{ route('agencies') }}" class="btn-outline-glow h-12 px-7 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 text-white font-semibold text-[15px] flex items-center tracking-wide">
                    View Directories
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ==================== QUICK LINKS & TASKS ==================== --}}
<section class="py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div data-animate class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-4">
                <div class="h-8 w-1 bg-gov-primary rounded-full"></div>
                <div>
                    <h3 class="font-display text-2xl lg:text-3xl font-bold tracking-[-0.02em] text-slate-900">Quick Links & Tasks</h3>
                    <p class="text-sm text-slate-500 mt-1 font-normal">Access commonly used government services</p>
                </div>
            </div>
            <a class="text-gov-primary font-bold flex items-center gap-1 hover:underline" href="{{ route('services') }}">
                All Services <span class="material-symbols-outlined text-sm">open_in_new</span>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($services as $index => $service)
                @php
                    $colorMap = [
                        'primary'   => ['bg' => 'bg-gov-primary/10', 'text' => 'text-gov-primary'],
                        'secondary' => ['bg' => 'bg-gov-secondary/10', 'text' => 'text-gov-secondary'],
                        'accent'    => ['bg' => 'bg-gov-accent/10', 'text' => 'text-amber-600'],
                        'neutral'   => ['bg' => 'bg-slate-200', 'text' => 'text-slate-700'],
                    ];
                    $colors = $colorMap[$service->color] ?? $colorMap['primary'];
                @endphp
                <a href="{{ $service->url }}" data-animate data-delay="{{ $index + 1 }}" class="group card-hover bg-slate-50 p-6 rounded-2xl border border-slate-100 hover:border-gov-primary/50 cursor-pointer block">
                    <div class="icon-hover size-14 rounded-xl {{ $colors['bg'] }} {{ $colors['text'] }} flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-3xl">{{ $service->icon }}</span>
                    </div>
                    <h4 class="text-base font-semibold mb-2 text-slate-900 tracking-[-0.01em]">{{ $service->title }}</h4>
                    <p class="text-slate-500 text-[13px] mb-4 leading-relaxed">{{ $service->description }}</p>
                    <div class="flex items-center {{ $colors['text'] }} text-[13px] font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        {{ $service->cta }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ==================== OFFICIAL ANNOUNCEMENTS ==================== --}}
<section class="py-16 bg-slate-50">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <div data-animate class="flex items-center gap-4 mb-10">
            <div class="h-8 w-1 bg-gov-secondary rounded-full"></div>
            <div>
                <h3 class="font-display text-2xl lg:text-3xl font-bold tracking-[-0.02em] text-slate-900">Official Announcements</h3>
                <p class="text-sm text-slate-500 mt-1 font-normal">Latest news and updates from the government</p>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- News Cards --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach ($announcements as $index => $news)
                    <div data-animate data-delay="{{ $index + 1 }}" class="group card-hover flex flex-col md:flex-row gap-6 bg-white p-4 rounded-2xl shadow-sm hover:border-gov-primary/30 border border-transparent">
                        <div class="w-full md:w-64 h-48 rounded-xl overflow-hidden flex-shrink-0">
                            <img alt="{{ $news['imageAlt'] }}" class="img-zoom w-full h-full object-cover" src="{{ $news['image'] }}" />
                        </div>
                        <div class="flex-1 flex flex-col justify-between py-2">
                            <div>
                                <span class="text-xs font-semibold text-gov-{{ $news['categoryColor'] }} uppercase tracking-wider mb-2 block">{{ $news['category'] }}</span>
                                <h4 class="font-display text-lg font-bold mb-2 leading-snug hover:text-gov-primary cursor-pointer transition-colors tracking-[-0.01em]">{{ $news['title'] }}</h4>
                                <p class="text-slate-500 text-[13px] line-clamp-3 leading-relaxed">{{ $news['excerpt'] }}</p>
                            </div>
                            <div class="flex items-center gap-4 mt-4">
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">calendar_today</span> {{ $news['date'] }}
                                </span>
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">share</span> Share
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Press Releases Sidebar --}}
            <div data-animate="fade-right" class="bg-gov-primary/5 rounded-2xl p-8 border border-gov-primary/10">
                <h4 class="font-display text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gov-primary">rss_feed</span> Recent Press Releases
                </h4>
                <div class="space-y-6">
                    @foreach ($pressReleases as $index => $release)
                        <div class="press-item rounded-lg px-3 py-1 -mx-3 {{ $index < count($pressReleases) - 1 ? 'border-b border-slate-200 pb-4' : 'pb-4' }}">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $release['source'] }}</span>
                            <a class="block text-sm font-medium mt-1 hover:text-gov-primary transition-colors leading-snug" href="{{ $release['url'] }}">{{ $release['title'] }}</a>
                        </div>
                    @endforeach
                    <button class="btn-glow w-full py-3 rounded-lg border-2 border-gov-primary text-gov-primary font-bold text-sm hover:bg-gov-primary hover:text-white">
                        View Media Center
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
