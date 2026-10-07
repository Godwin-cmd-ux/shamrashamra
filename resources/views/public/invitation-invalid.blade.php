<x-guest-layout>
    <div class="mx-auto max-w-md px-4 py-12 text-center sm:px-0">
        <div class="card p-8">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-7 w-7 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>

            <h1 class="mt-5 font-display text-xl font-semibold text-zinc-900">
                {{ __('invitations.guest_view.'.$reason.'_title') }}
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-zinc-500">
                {{ __('invitations.guest_view.'.$reason.'_body') }}
            </p>

            <a href="{{ route('home') }}" class="btn btn-secondary mt-6">{{ __('common.back') }}</a>
        </div>
    </div>
</x-guest-layout>
