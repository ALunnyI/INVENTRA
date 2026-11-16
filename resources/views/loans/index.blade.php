<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Peminjaman Aset</h2></x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ $errors->first() }}</div>
        @endif

        <div class="flex flex-wrap justify-between gap-3 mb-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama atau kode aset..." class="border-gray-300 rounded-md">
                <select name="status" class="border-gray-300 rounded-md">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Loan::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 bg-gray-700 text-white rounded">Filter</button>
                <a href="{{ route('loans.index') }}" class="px-3 py-2 text-gray-600">Reset</a>
            </form>

            <a href="{{ route('loans.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">+ Pinjam Aset</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">Aset</th>
                        <th class="p-3">Peminjam</th>
                        <th class="p-3">Tgl. Pinjam</th>
                        <th class="p-3">Jatuh Tempo</th>
                        <th class="p-3">Tgl. Kembali</th>
                        <th class="p-3">Status</th>
                        @if (auth()->user()->isAdmin())
                            <th class="p-3">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse ($loans as $loan)
                    @php
                        $badge = match ($loan->status) {
                            'dipinjam'  => 'bg-yellow-100 text-yellow-800',
                            'terlambat' => 'bg-red-100 text-red-800',
                            default     => 'bg-green-100 text-green-800',
                        };
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">
                            {{ $loan->asset->name }}
                            <div class="text-xs text-gray-500">{{ $loan->asset->asset_code }}</div>
                        </td>
                        <td class="p-3">{{ $loan->user->name }}</td>
                        <td class="p-3">{{ $loan->loan_date->format('d M Y') }}</td>
                        <td class="p-3">{{ $loan->due_date->format('d M Y') }}</td>
                        <td class="p-3">{{ $loan->returned_at?->format('d M Y') ?? '-' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-sm {{ $badge }}">
                                {{ \App\Models\Loan::STATUS_LABELS[$loan->status] }}
                            </span>
                            @if ($loan->status === 'terlambat')
                                <div class="text-xs text-red-700 mt-1">
                                    {{ (int) $loan->due_date->diffInDays(today()) }} hari
                                </div>
                            @endif
                        </td>
                        @if (auth()->user()->isAdmin())
                            <td class="p-3">
                                @unless ($loan->returned_at)
                                    <form method="POST" action="{{ route('loans.return', $loan) }}"
                                          class="flex gap-2"
                                          onsubmit="return confirm('Catat pengembalian aset ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <select name="condition" class="border-gray-300 rounded-md text-sm">
                                            @foreach (\App\Models\Asset::CONDITIONS as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected($loan->asset->condition === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Kembalikan</button>
                                    </form>
                                @endunless
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-3 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $loans->links() }}</div>
    </div>
</x-app-layout>
