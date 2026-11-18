@props([
    'title',
    'filters' => [],
    'orientation' => 'portrait',
    'csvUrl' => null,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css'])

    <style>
        @page { size: A4 {{ $orientation }}; margin: 12mm; }

        table.report { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.report th, table.report td { border: 1px solid #cbd5e1; padding: 5px 7px; vertical-align: top; }
        table.report thead { display: table-header-group; background: #eef2ff; }
        table.report tr { break-inside: avoid; page-break-inside: avoid; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-100 text-gray-900 antialiased">

    {{-- Toolbar (tidak ikut tercetak) --}}
    <div class="no-print sticky top-0 z-10 border-b border-indigo-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            @php
                $back = url()->previous(route('reports.index'));

                // Hindari tautan yang mengarah ke halaman ini sendiri (misalnya setelah refresh tanpa referer)
                if ($back === url()->full()) {
                    $back = route('reports.index');
                }
            @endphp

            <a href="{{ $back }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                &larr; Kembali
            </a>
            <div class="flex gap-2">
                @if ($csvUrl)
                    <a href="{{ $csvUrl }}"
                       class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Unduh CSV
                    </a>
                @endif
                <button type="button" onclick="window.print()"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <main class="mx-auto my-6 max-w-6xl bg-white p-8 shadow-sm ring-1 ring-gray-200 print:my-0 print:max-w-none print:p-0 print:shadow-none print:ring-0">

        {{-- Kop laporan --}}
        <header class="border-b-2 border-indigo-900 pb-3 text-center">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-900">{{ config('app.name') }}</p>
            <h1 class="mt-1 text-xl font-bold uppercase">{{ $title }}</h1>
            <p class="mt-1 text-xs text-gray-600">
                {{ count($filters) ? implode('  |  ', $filters) : 'Seluruh data' }}
            </p>
            <p class="text-xs text-gray-500">
                Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} oleh {{ auth()->user()->name }}
            </p>
        </header>

        <div class="mt-5">
            {{ $slot }}
        </div>

        {{-- Tanda tangan --}}
        <div class="mt-10 grid grid-cols-2 gap-8 text-sm" style="break-inside: avoid;">
            <div class="text-center">
                <p>&nbsp;</p>
                <p>Mengetahui,</p>
                <div class="h-16"></div>
                <p>(................................)</p>
            </div>
            <div class="text-center">
                <p>{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                <p>Dibuat oleh,</p>
                <div class="h-16"></div>
                <p class="font-semibold underline">{{ auth()->user()->name }}</p>
            </div>
        </div>
    </main>
</body>
</html>
