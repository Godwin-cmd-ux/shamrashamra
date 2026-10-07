<nav x-data="{ open: false }" class="border-b border-zinc-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-800 font-display text-lg font-semibold text-gold-300">S</span>
                    <span class="hidden font-display text-lg font-semibold text-zinc-900 sm:block">ShamraShamra</span>
                </a>

                <div class="hidden items-center gap-6 sm:flex">
                    <a href="{{ route('dashboard') }}"
                       @class(['text-sm font-semibold transition', request()->routeIs('dashboard') ? 'text-brand-700' : 'text-zinc-500 hover:text-zinc-900'])>
                        {{ __('common.dashboard') }}
                    </a>
                    <a href="{{ route('events.index') }}"
                       @class(['text-sm font-semibold transition', request()->routeIs('events.*') ? 'text-brand-700' : 'text-zinc-500 hover:text-zinc-900'])>
                        {{ __('common.events') }}
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           @class(['text-sm font-semibold transition', request()->routeIs('admin.*') ? 'text-brand-700' : 'text-zinc-500 hover:text-zinc-900'])>
                            {{ __('dashboard.platform_admin') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'en' ? 'sw' : 'en') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" title="{{ __('common.language') }}">
                        {{ app()->getLocale() === 'en' ? 'SW' : 'EN' }}
                    </button>
                </form>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="flex items-center gap-2 rounded-full py-1 pe-1 ps-2 text-sm font-semibold text-zinc-600 hover:text-zinc-900">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-bold uppercase text-brand-700">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="hidden max-w-[10rem] truncate md:block">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('common.profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-zinc-600 hover:bg-zinc-100 focus:outline-none focus:bg-zinc-100">
                                {{ __('common.log_out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button type="button" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 sm:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
        </div>

        <div class="border-t border-zinc-100 py-3 sm:hidden" x-show="open" x-cloak>
            <div class="flex flex-col gap-1">
                <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">{{ __('common.dashboard') }}</a>
                <a href="{{ route('events.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">{{ __('common.events') }}</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">{{ __('dashboard.platform_admin') }}</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">{{ __('common.profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-start text-sm font-semibold text-zinc-700 hover:bg-zinc-50">{{ __('common.log_out') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>
