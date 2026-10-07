@if (session('status'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
        <p class="font-semibold">{{ $errors->first() }}</p>
        @if ($errors->count() > 1)
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    @if ($error !== $errors->first())
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
@endif
