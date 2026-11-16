<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Dashboard</h2></x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <p class="text-gray-600">Selamat datang, {{ auth()->user()->name }}.</p>

        {{-- Ringkasan aset --}}
        <section>
            <h3 class="font-semibold mb-2">Ringkasan Aset</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-stat-card label="Total Aset" :value="$stats['total']"
                             :href="route('assets.index')" />
                <x-stat-card label="Tersedia" :value="$stats['tersedia']" tone="green"
                             :href="route('assets.index', ['status' => 'tersedia'])" />
                <x-stat-card label="Dipinjam" :value="$stats['dipinjam']" tone="yellow"
                             :href="route('assets.index', ['status' => 'dipinjam'])" />
                <x-stat-card label="Maintenance" :value="$stats['maintenance']" tone="indigo"
                             :href="route('assets.index', ['status' => 'maintenance'])" />
            </div>
        </section>

        {{-- Ringkasan peminjaman --}}
        <section>
            <h3 class="font-semibold mb-2">
                {{ auth()->user()->isAdmin() ? 'Ringkasan Peminjaman' : 'Peminjaman Saya' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card label="Peminjaman Aktif" :value="$activeLoans" tone="yellow"
                             :href="route('loans.index', ['status' => 'dipinjam'])" />
                <x-stat-card label="Jatuh Tempo ≤ 3 Hari" :value="$dueSoon" />
                <x-stat-card label="Terlambat" :value="$overdueLoans" tone="red"
                             :href="route('loans.index', ['status' => 'terlambat'])" />
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Perlu perhatian --}}
            <div class="bg-white shadow rounded p-4">
                <h3 class="font-semibold mb-2">Perlu Perhatian</h3>
                <ul class="divide-y">
                    @forelse ($attentionLoans as $loan)
                        <li class="py-2 flex justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $loan->asset->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $loan->asset->asset_code }}
                                    @if (auth()->user()->isAdmin())
                                        &middot; {{ $loan->user->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right text-sm">
                                @if ($loan->status === 'terlambat')
                                    <span class="text-red-700">
                                        Terlambat {{ (int) $loan->due_date->diffInDays(today()) }} hari
                                    </span>
                                @else
                                    <span class="text-yellow-700">
                                        Jatuh tempo {{ $loan->due_date->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">Tidak ada peminjaman yang perlu perhatian.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Peminjaman terbaru --}}
            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold">Peminjaman Terbaru</h3>
                    <a href="{{ route('loans.index') }}" class="text-xs text-indigo-600">Semua &rarr;</a>
                </div>
                <ul class="divide-y">
                    @forelse ($recentLoans as $loan)
                        @php
                            $badge = match ($loan->status) {
                                'dipinjam'  => 'bg-yellow-100 text-yellow-800',
                                'terlambat' => 'bg-red-100 text-red-800',
                                default     => 'bg-green-100 text-green-800',
                            };
                        @endphp
                        <li class="py-2 flex justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $loan->asset->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $loan->user->name }} &middot; {{ $loan->loan_date->format('d M Y') }}
                                </p>
                            </div>
                            <span class="self-start px-2 py-1 rounded text-xs {{ $badge }}">
                                {{ \App\Models\Loan::STATUS_LABELS[$loan->status] }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">Belum ada peminjaman.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Khusus admin --}}
        @if (auth()->user()->isAdmin())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold mb-3">Aset per Kategori</h3>
                    @php $max = max($categories->max('assets_count') ?? 0, 1); @endphp
                    <div class="space-y-3">
                        @forelse ($categories as $category)
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-gray-500">{{ $category->assets_count }}</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded">
                                    <div class="h-2 bg-indigo-500 rounded"
                                         style="width: {{ ($category->assets_count / $max) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold mb-3">Kondisi Aset</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        @foreach (\App\Models\Asset::CONDITIONS as $value => $label)
                            <div>
                                <p class="text-2xl font-bold {{ $value === 'baik' ? 'text-green-700' : ($value === 'rusak_ringan' ? 'text-yellow-700' : 'text-red-700') }}">
                                    {{ $conditionCounts->get($value, 0) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
