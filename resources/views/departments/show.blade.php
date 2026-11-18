<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl">Departemen {{ $department->name }}</h2>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('departments.edit', $department) }}"
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">Edit</a>
                <a href="{{ route('departments.index') }}"
                   class="px-3 py-1.5 text-indigo-600 hover:text-indigo-800">&larr; Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <x-stat-card label="Anggota" :value="$stats['members']" tone="indigo" />
            <x-stat-card label="Sedang Meminjam" :value="$stats['borrowers']" tone="yellow" />
            <x-stat-card label="Aset Dipinjam" :value="$stats['borrowed']" tone="gray" />
            <x-stat-card label="Terlambat" :value="$stats['overdue']" tone="red" />
        </div>

        {{-- Daftar anggota --}}
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-indigo-50 text-indigo-900">
                    <tr>
                        <th class="p-3">Anggota</th>
                        <th class="p-3">Kontak</th>
                        <th class="p-3">Aset yang Sedang Dipinjam</th>
                        <th class="p-3">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    @php
                        $message = $user->reminderMessage(auth()->user()->name);
                        $loans   = $user->activeLoans->sortBy('due_date');
                    @endphp
                    <tr class="border-t align-top {{ $user->overdue_loans_count > 0 ? 'bg-red-50/40' : '' }}">

                        <td class="p-3">
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                {{ $user->isAdmin() ? 'Admin' : 'Staff' }}
                            </span>
                        </td>

                        <td class="p-3">
                            <a href="mailto:{{ $user->email }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $user->email }}
                            </a>
                            @if ($user->phone)
                                <div class="mt-1 text-gray-700">
                                    {{ $user->phone }}
                                    @if ($user->whatsappUrl())
                                        <a href="{{ $user->whatsappUrl() }}" target="_blank" rel="noopener"
                                           class="ml-1 text-xs text-green-700 hover:underline">WhatsApp</a>
                                    @endif
                                </div>
                            @else
                                <div class="mt-1 text-xs text-gray-400">Nomor HP belum diisi</div>
                            @endif
                        </td>

                        <td class="p-3">
                            @forelse ($loans as $loan)
                                <div class="mb-2 last:mb-0">
                                    <a href="{{ route('assets.show', $loan->asset) }}"
                                       class="font-medium text-gray-800 hover:text-indigo-700">
                                        {{ $loan->asset->name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ $loan->asset->asset_code }}</span>
                                    <div class="text-xs text-gray-600">
                                        Dipinjam {{ $loan->loan_date->format('d/m/Y') }}
                                        &middot; jatuh tempo {{ $loan->due_date->format('d/m/Y') }}
                                        @if ($loan->status === 'terlambat')
                                            <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 font-medium text-red-800">
                                                Terlambat {{ (int) $loan->due_date->diffInDays(today()) }} hari
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="text-gray-400">Tidak sedang meminjam</span>
                            @endforelse
                        </td>

                        <td class="p-3">
                            @if ($message)
                                <div class="flex flex-col items-start gap-2">
                                    @if ($user->whatsappUrl($message))
                                        <a href="{{ $user->whatsappUrl($message) }}" target="_blank" rel="noopener"
                                           class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                            Ingatkan via WhatsApp
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Tanpa nomor HP</span>
                                    @endif
                                    <a href="{{ $user->emailUrl($message) }}"
                                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                        Ingatkan via Email
                                    </a>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            Belum ada anggota di departemen ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-500">
            Anggota dengan peminjaman terlambat ditampilkan paling atas. Tombol "Ingatkan" membuka WhatsApp atau
            aplikasi email dengan pesan yang sudah terisi. Pesan baru terkirim setelah kamu menekan kirim di sana.
        </p>
    </div>
</x-app-layout>
