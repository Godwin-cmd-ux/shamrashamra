<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'ShamraShamra'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600|instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col bg-stone-50">
            <header class="border-b border-zinc-200 bg-white">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-800 font-display text-lg font-semibold text-gold-300">S</span>
                        <span class="font-display text-lg font-semibold text-zinc-900">ShamraShamra</span>
                    </a>

                    <nav class="flex items-center gap-2 sm:gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm sm:btn">{{ __('common.dashboard') }}</a>
                        @else
                            <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'en' ? 'sw' : 'en') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm sm:btn" title="{{ __('common.language') }}">
                                    {{ app()->getLocale() === 'en' ? 'SW' : 'EN' }}
                                </button>
                            </form>
                            <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-zinc-600 hover:text-zinc-900 sm:inline">{{ __('common.sign_in') }}</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm sm:btn">{{ __('common.sign_up') }}</a>
                        @endauth
                    </nav>
                </div>
            </header>

            @include('layouts.flash')

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-zinc-200 bg-white">
                <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-zinc-500 sm:flex-row sm:px-6">
                    <p>&copy; {{ date('Y') }} shamrashamra.com · {{ __('landing.footer.made_in') }}</p>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('home') }}#faq" class="hover:text-zinc-800">{{ __('landing.nav.faq') }}</a>
                        <a href="mailto:{{ __('landing.footer.contact_email') }}" class="hover:text-zinc-800">{{ __('landing.footer.contact') }}</a>
                        <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'en' ? 'sw' : 'en') }}">
                            @csrf
                            <button type="submit" class="font-semibold hover:text-zinc-800">{{ app()->getLocale() === 'en' ? 'Kiswahili' : 'English' }}</button>
                        </form>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
