<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ __('landing.hero.subtitle') }}">

        <title>ShamraShamra — {{ __('landing.hero.title_a') }} {{ __('landing.hero.title_b') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600|instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .hero-pattern {
                background-image:
                    radial-gradient(circle at 15% 20%, rgba(201, 162, 39, 0.14), transparent 40%),
                    radial-gradient(circle at 85% 10%, rgba(95, 166, 142, 0.18), transparent 45%),
                    radial-gradient(circle at 70% 85%, rgba(201, 162, 39, 0.10), transparent 40%),
                    linear-gradient(135deg, #0d211d 0%, #17352d 55%, #0d211d 100%);
            }
            .hero-dots {
                background-image: radial-gradient(rgba(242, 227, 179, 0.18) 1px, transparent 1px);
                background-size: 26px 26px;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        {{-- Header --}}
        <header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-800 font-display text-lg font-semibold text-gold-300">S</span>
                    <span class="font-display text-lg font-semibold text-zinc-900">ShamraShamra</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-semibold text-zinc-600 md:flex">
                    <a href="#features" class="hover:text-zinc-900">{{ __('landing.nav.features') }}</a>
                    <a href="#how" class="hover:text-zinc-900">{{ __('landing.nav.how') }}</a>
                    <a href="#styles" class="hover:text-zinc-900">{{ __('landing.nav.styles') }}</a>
                    <a href="#faq" class="hover:text-zinc-900">{{ __('landing.nav.faq') }}</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'en' ? 'sw' : 'en') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="{{ __('common.language') }}">
                            {{ app()->getLocale() === 'en' ? 'SW' : 'EN' }}
                        </button>
                    </form>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm sm:btn">{{ __('common.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-zinc-700 hover:text-zinc-900 sm:block">{{ __('landing.nav.login') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm sm:btn">{{ __('landing.nav.register') }}</a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="hero-pattern relative overflow-hidden text-zinc-100">
            <div class="hero-dots absolute inset-0 opacity-40"></div>
            <div class="relative mx-auto grid max-w-6xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-28">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-gold-400/40 bg-gold-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gold-300">
                        {{ __('landing.hero.badge') }}
                    </p>
                    <h1 class="mt-6 font-display text-4xl font-semibold leading-tight text-white sm:text-5xl">
                        {{ __('landing.hero.title_a') }}<br>
                        <span class="text-gold-300">{{ __('landing.hero.title_b') }}</span>
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-brand-100/90 sm:text-lg">
                        {{ __('landing.hero.subtitle') }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn btn-gold px-6 py-3 text-base">{{ __('landing.hero.cta_primary') }}</a>
                        <a href="#how" class="btn px-6 py-3 text-base text-white ring-1 ring-inset ring-white/30 hover:bg-white/10">{{ __('landing.hero.cta_secondary') }}</a>
                    </div>
                    <p class="mt-4 text-xs uppercase tracking-wider text-brand-200/70">{{ __('landing.hero.note') }}</p>
                </div>

                {{-- Invitation preview card --}}
                <div class="mx-auto w-full max-w-sm">
                    <div class="rotate-1 rounded-3xl border border-gold-400/30 bg-[#12261f] p-8 shadow-2xl">
                        <p class="text-center text-[11px] uppercase tracking-[0.3em] text-gold-400">{{ __('invitations.guest_view.title') }}</p>
                        <div class="my-4 flex items-center justify-center gap-3">
                            <span class="h-px w-10 bg-gold-400/50"></span>
                            <span class="text-gold-400">&#10022;</span>
                            <span class="h-px w-10 bg-gold-400/50"></span>
                        </div>
                        <p class="text-center font-display text-2xl text-white">Neema &amp; Juma</p>
                        <p class="mt-2 text-center text-sm text-brand-100/70">Harusi · 12 Desemba 2026</p>
                        <p class="mt-1 text-center text-xs text-brand-100/60">Kilimanjaro Hall, Dar es Salaam</p>
                        <div class="mt-6 flex justify-center">
                            <div class="grid h-24 w-24 grid-cols-6 gap-0.5 rounded-lg bg-white p-2" aria-hidden="true">
                                @foreach ([1,0,1,1,0,1, 0,1,1,0,1,0, 1,1,0,1,0,1, 0,1,0,1,1,0, 1,0,1,0,1,1, 0,1,1,0,1,0] as $cell)
                                    <span class="{{ $cell ? 'bg-[#12261f]' : 'bg-transparent' }}"></span>
                                @endforeach
                            </div>
                        </div>
                        <p class="mt-4 text-center text-[10px] uppercase tracking-widest text-gold-400/80">shamrashamra.com</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="font-display text-3xl font-semibold text-zinc-900">{{ __('landing.features.title') }}</h2>
                    <p class="mt-3 text-zinc-500">{{ __('landing.features.subtitle') }}</p>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $icons = [
                            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
                            'card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M7 15c2-4 4-4 5-1s3 3 5-1"/>',
                            'qr' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14h1M14 20h3M20 17v4"/>',
                            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                            'wallet' => '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/>',
                            'chart' => '<path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 5-8"/>',
                        ];
                        $features = [
                            ['events_title', 'events_desc'],
                            ['invites_title', 'invites_desc'],
                            ['qr_title', 'qr_desc'],
                            ['rsvp_title', 'rsvp_desc'],
                            ['finance_title', 'finance_desc'],
                            ['reports_title', 'reports_desc'],
                        ];
                    @endphp

                    @foreach ($features as [$titleKey, $descKey])
                        <div class="group rounded-2xl border border-zinc-200 bg-stone-50 p-6 transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-white hover:shadow-soft">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-800 text-gold-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                    {!! $icons[match($titleKey) { 'events_title' => 'calendar', 'invites_title' => 'card', 'qr_title' => 'qr', 'rsvp_title' => 'users', 'finance_title' => 'wallet', default => 'chart' }] !!}
                                </svg>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-semibold text-zinc-900">{{ __('landing.features.'.$titleKey) }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ __('landing.features.'.$descKey) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="how" class="border-y border-zinc-200 bg-brand-50/60 py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="font-display text-3xl font-semibold text-zinc-900">{{ __('landing.how.title') }}</h2>
                    <p class="mt-3 text-zinc-500">{{ __('landing.how.subtitle') }}</p>
                </div>

                <ol class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([1, 2, 3, 4] as $step)
                        <li class="relative rounded-2xl bg-white p-6 shadow-soft ring-1 ring-zinc-200/70">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 font-display text-sm font-semibold text-white">{{ $step }}</span>
                            <h3 class="mt-4 font-semibold text-zinc-900">{{ __('landing.how.step'.$step.'_title') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ __('landing.how.step'.$step.'_desc') }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Invitation styles --}}
        <section id="styles" class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="font-display text-3xl font-semibold text-zinc-900">{{ __('landing.styles.title') }}</h2>
                    <p class="mt-3 text-zinc-500">{{ __('landing.styles.subtitle') }}</p>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($templates as $template)
                        @php $cfg = $template->config; @endphp
                        <div class="flex aspect-[3/4] flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-soft"
                             style="background: {{ $cfg['background'] }}; border-color: {{ $cfg['accent'] }}55; color: {{ $cfg['text'] }};">
                            <p class="text-center text-[9px] uppercase tracking-[0.25em]" style="color: {{ $cfg['accent'] }};">ShamraShamra</p>
                            <div class="text-center">
                                <p class="font-display text-base leading-snug" style="font-family: {{ $cfg['heading_font'] }};">{{ $template->name }}</p>
                                <span class="mx-auto mt-2 block h-px w-8" style="background: {{ $cfg['accent'] }};"></span>
                                <p class="mt-2 text-[10px]" style="color: {{ $cfg['muted'] }};">{{ $template->style }}</p>
                            </div>
                            <p class="text-center text-[9px]" style="color: {{ $cfg['muted'] }};">&#10022;</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-center text-sm text-zinc-500">{{ __('landing.styles.note') }}</p>
            </div>
        </section>

        {{-- Event types --}}
        <section class="bg-brand-950 py-16 text-center text-zinc-100">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <h2 class="font-display text-2xl font-semibold text-white sm:text-3xl">{{ __('landing.types.title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-brand-100/80 sm:text-base">{{ __('landing.types.subtitle') }}</p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach ($categories as $category)
                        <span class="rounded-full border border-gold-400/30 bg-white/5 px-3 py-1 text-xs font-semibold text-gold-200">{{ $category->name() }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="bg-white py-16">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-semibold text-zinc-900">{{ __('landing.cta.title') }}</h2>
                <p class="mt-3 text-zinc-500">{{ __('landing.cta.subtitle') }}</p>
                <a href="{{ route('register') }}" class="btn btn-primary mt-6 px-8 py-3 text-base">{{ __('landing.cta.button') }}</a>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="border-t border-zinc-200 bg-stone-50 py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <h2 class="text-center font-display text-3xl font-semibold text-zinc-900">{{ __('landing.faq.title') }}</h2>

                <div class="mt-8 divide-y divide-zinc-200 rounded-2xl bg-white shadow-soft ring-1 ring-zinc-200/70">
                    @foreach ([1, 2, 3, 4, 5, 6] as $i)
                        <details class="group px-6 py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-zinc-800">
                                {{ __('landing.faq.q'.$i) }}
                                <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            </summary>
                            <p class="mt-3 text-sm leading-relaxed text-zinc-600">{{ __('landing.faq.a'.$i) }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-brand-950 py-12 text-brand-100/70">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:grid-cols-3 sm:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold-500 font-display text-lg font-semibold text-brand-950">S</span>
                        <span class="font-display text-lg font-semibold text-white">ShamraShamra</span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed">{{ __('landing.footer.tagline') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gold-300">{{ __('landing.footer.product') }}</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white">{{ __('landing.footer.features') }}</a></li>
                        <li><a href="#how" class="hover:text-white">{{ __('landing.footer.how') }}</a></li>
                        <li><a href="#faq" class="hover:text-white">{{ __('landing.footer.faq') }}</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white">{{ __('landing.nav.register') }}</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gold-300">{{ __('landing.footer.support') }}</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="mailto:{{ __('landing.footer.contact_email') }}" class="hover:text-white">{{ __('landing.footer.contact_email') }}</a></li>
                    </ul>
                    <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'en' ? 'sw' : 'en') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-gold-300 hover:text-gold-200">
                            {{ app()->getLocale() === 'en' ? 'Lugha: Kiswahili' : 'Language: English' }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="mx-auto mt-10 max-w-6xl border-t border-white/10 px-4 pt-6 text-xs sm:px-6">
                &copy; {{ date('Y') }} shamrashamra.com · {{ __('landing.footer.rights') }}
            </div>
        </footer>
    </body>
</html>
