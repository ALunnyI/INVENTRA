@props(['label', 'value', 'href' => null, 'tone' => 'gray'])

@php
    $tones = [
        'gray'   => 'text-gray-800',
        'green'  => 'text-green-700',
        'yellow' => 'text-yellow-700',
        'red'    => 'text-red-700',
        'indigo' => 'text-indigo-700',
    ];
@endphp

<div class="bg-white shadow rounded p-4">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="text-3xl font-bold {{ $tones[$tone] ?? $tones['gray'] }}">{{ $value }}</p>
    @if ($href)
        <a href="{{ $href }}" class="text-xs text-indigo-600">Lihat &rarr;</a>
    @endif
</div>
