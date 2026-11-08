@props(['label', 'value', 'hint' => null, 'color' => 'indigo'])

@php
    // Class ditulis utuh agar terdeteksi Tailwind saat build.
    $iconClasses = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ][$color] ?? 'bg-gray-100 text-gray-600';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5']) }}>
    @isset($icon)
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $iconClasses }}" aria-hidden="true">
            {{ $icon }}
        </div>
    @endisset

    <div class="min-w-0">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <p class="mt-1 truncate text-2xl font-semibold tracking-tight text-gray-900">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
        @endif
    </div>
</div>
