@props(['label', 'value', 'hint' => null, 'tone' => 'default'])

@php
    $tones = [
        'default' => 'text-zinc-900',
        'positive' => 'text-emerald-700',
        'caution' => 'text-amber-700',
        'negative' => 'text-rose-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card p-5']) }}>
    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $label }}</p>
    <p class="mt-2 font-display text-2xl font-semibold {{ $tones[$tone] ?? $tones['default'] }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-zinc-400">{{ $hint }}</p>
    @endif
</div>
