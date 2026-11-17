<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Dashboard</h2></x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Banner sambutan --}}
        <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg sm:p-8"
             style="background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 55%, #4f46e5 100%);">
            <div class="absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-20 right-24 h-48 w-48 rounded-full bg-white/5"></div>

            <div class="relative z-10">
                <p class="text-sm text-indigo-200">{{ now()->format('d M Y') }}</p>
                <h3 class="mt-1 text-2xl font-bold">Halo, {{ auth()->user()->name }}</h3>
                <p class="mt-1 text-sm text-indigo-100">
                    @if ($overdueLoans > 0)
                        Ada <strong>{{ $overdueLoans }}</strong>
                        {{ auth()->user()->isAdmin() ? 'peminjaman' : 'peminjamanmu' }}
                        yang terlambat dikembalikan.
                    @else
                        Semua peminjaman berjalan sesuai jadwal.
                    @endif
                </p>
                <a href="{{ route('loans.create') }}"
                   class="mt-4 inline-block rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow transition hover:bg-indigo-50">
                    + Pinjam Aset
                </a>
            </div>
        </div>

        {{-- Ringkasan aset --}}
        <section>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-indigo-900/70">Ringkasan Aset</h3>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <x-stat-card label="Total Aset" :value="$stats['total']" tone="indigo"
                             :href="route('assets.index')" />
                <x-stat-card label="Tersedia" :value="$stats['tersedia']" tone="green"
                             :href="route('assets.index', ['status' => 'tersedia'])" />
                <x-stat-card label="Dipinjam" :value="$stats['dipinjam']" tone="yellow"
                             :href="route('assets.index', ['status' => 'dipinjam'])" />
                <x-stat-card label="Maintenance" :value="$stats['maintenance']" tone="gray"
                             :href="route('assets.index', ['status' => 'maintenance'])" />
            </div>
        </section>

        {{-- Ringkasan peminjaman --}}
        <section>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-indigo-900/70">
                {{ auth()->user()->isAdmin() ? 'Ringkasan Peminjaman' : 'Peminjaman Saya' }}
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card label="Peminjaman Aktif" :value="$activeLoans" tone="indigo"
                             :href="route('loans.index', ['status' => 'dipinjam'])" />
                <x-stat-card label="Jatuh Tempo ≤ 3 Hari" :value="$dueSoon" tone="yellow" />
                <x-stat-card label="Terlambat" :value="$overdueLoans" tone="red"
                             :href="route('loans.index', ['status' => 'terlambat'])" />
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Perlu perhatian --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-2 font-semibold text-indigo-900">Perlu Perhatian</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($attentionLoans as $loan)
                        <li class="flex justify-between gap-3 py-3">
                            <div>
                                <p class="font-medium text-gray-800">{{ $loan->asset->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $loan->asset->asset_code }}
                                    @if (auth()->user()->isAdmin())
                                        &middot; {{ $loan->user->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right text-sm">
                                @if ($loan->status === 'terlambat')
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                        Terlambat {{ (int) $loan->due_date->diffInDays(today()) }} hari
                                    </span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">
                                        Jatuh tempo {{ $loan->due_date->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">Tidak ada peminjaman yang perlu perhatian.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Peminjaman terbaru --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="font-semibold text-indigo-900">Peminjaman Terbaru</h3>
                    <a href="{{ route('loans.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                        Semua &rarr;
                    </a>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($recentLoans as $loan)
                        @php
                            $badge = match ($loan->status) {
                                'dipinjam'  => 'bg-yellow-100 text-yellow-800',
                                'terlambat' => 'bg-red-100 text-red-800',
                                default     => 'bg-green-100 text-green-800',
                            };
                        @endphp
                        <li class="flex justify-between gap-3 py-3">
                            <div>
                                <p class="font-medium text-gray-800">{{ $loan->asset->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $loan->user->name }} &middot; {{ $loan->loan_date->format('d M Y') }}
                                </p>
                            </div>
                            <span class="self-start rounded-full px-2 py-1 text-xs font-medium {{ $badge }}">
                                {{ \App\Models\Loan::STATUS_LABELS[$loan->status] }}
                            </span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">Belum ada peminjaman.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Khusus admin --}}
        @if (auth()->user()->isAdmin())
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-4 font-semibold text-indigo-900">Aset per Kategori</h3>
                    @php $max = max($categories->max('assets_count') ?? 0, 1); @endphp
                    <div class="space-y-4">
                        @forelse ($categories as $category)
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">{{ $category->name }}</span>
                                    <span class="font-medium text-indigo-700">{{ $category->assets_count }}</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-indigo-50">
                                    <div class="h-2 rounded-full bg-indigo-500"
                                         style="width: {{ ($category->assets_count / $max) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-4 font-semibold text-indigo-900">Kondisi Aset</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        @foreach (\App\Models\Asset::CONDITIONS as $value => $label)
                            @php
                                $color = match ($value) {
                                    'baik'         => 'text-green-700 bg-green-50',
                                    'rusak_ringan' => 'text-yellow-700 bg-yellow-50',
                                    default        => 'text-red-700 bg-red-50',
                                };
                            @endphp
                            <div class="rounded-lg py-4 {{ $color }}">
                                <p class="text-3xl font-bold">{{ $conditionCounts->get($value, 0) }}</p>
                                <p class="mt-1 text-xs">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
