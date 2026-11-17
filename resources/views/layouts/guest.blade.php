<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $features = [
                'Pencatatan aset lengkap dengan foto, kondisi, dan status',
                'Peminjaman terpantau, termasuk jatuh tempo dan keterlambatan',
                'Dashboard ringkas untuk admin dan staff',
            ];
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-2">

            {{-- Panel brand (hanya tampil di layar lebar) --}}
            <div class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 text-white"
                 style="background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 55%, #4f46e5 100%);">

                {{-- Hiasan lingkaran --}}
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/5"></div>

                <div class="relative z-10 flex items-center gap-3">
                    <div class="rounded-xl bg-white p-2 shadow-lg">
                        <x-application-logo class="h-10 w-10 object-contain" />
                    </div>
                    <span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span>
                </div>

                <div class="relative z-10 max-w-md">
                    <h2 class="text-4xl font-bold leading-tight">
                        Kelola aset TI dengan lebih tertata.
                    </h2>
                    <p class="mt-4 text-indigo-100">
                        Satu tempat untuk mencatat perangkat, memantau peminjaman, dan menjaga
                        ketersediaan aset perusahaan.
                    </p>

                    <ul class="mt-8 space-y-4 text-sm text-indigo-50">
                        @foreach ($features as $feature)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative z-10 text-xs text-indigo-200">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </div>

            {{-- Panel form --}}
            <div class="flex min-h-screen items-center justify-center bg-gray-50 px-6 py-12 lg:min-h-0">
                <div class="w-full max-w-md">

                    {{-- Logo untuk layar kecil --}}
                    <div class="mb-8 text-center lg:hidden">
                        <x-application-logo class="mx-auto h-16 w-16 object-contain" />
                        <p class="mt-3 text-lg font-semibold text-gray-800">{{ config('app.name') }}</p>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-lg">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
