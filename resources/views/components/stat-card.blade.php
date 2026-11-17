@props(['label', 'value', 'href' => null, 'tone' => 'gray'])

@php
    $tones = [
        'gray'   => ['bar' => 'border-l-slate-400',  'text' => 'text-slate-800'],
        'green'  => ['bar' => 'border-l-green-500',  'text' => 'text-green-700'],
        'yellow' => ['bar' => 'border-l-yellow-500', 'text' => 'text-yellow-700'],
        'red'    => ['bar' => 'border-l-red-500',    'text' => 'text-red-700'],
        'indigo' => ['bar' => 'border-l-indigo-500', 'text' => 'text-indigo-700'],
    ];
    $t = $tones[$tone] ?? $tones['gray'];
@endphp

<div class="rounded-xl border-l-4 {{ $t['bar'] }} bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-bold {{ $t['text'] }}">{{ $value }}</p>
    @if ($href)
        <a href="{{ $href }}" class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800">
            Lihat &rarr;
        </a>
    @endif
</div>
