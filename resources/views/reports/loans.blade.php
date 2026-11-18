<x-print-layout title="Laporan Peminjaman Aset" orientation="landscape" :filters="$filters"
                :csv-url="route('reports.loans.csv', request()->query())">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-3 text-center text-sm sm:grid-cols-4">
        <div class="rounded border border-gray-300 p-2">
            <p class="text-xs text-gray-500">Total Peminjaman</p>
            <p class="text-xl font-bold">{{ $loans->count() }}</p>
        </div>
        @foreach (\App\Models\Loan::STATUS_LABELS as $key => $label)
            <div class="rounded border border-gray-300 p-2">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-xl font-bold">{{ $byStatus->get($key, 0) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Tabel --}}
    <table class="report mt-4">
        <thead>
            <tr>
                <th class="w-8">No</th>
                <th>Aset</th>
                <th>Peminjam</th>
                <th>Departemen</th>
                <th>Tgl. Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl. Kembali</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        {{ $loan->asset->name }}
                        <span class="block text-gray-500">{{ $loan->asset->asset_code }}</span>
                    </td>
                    <td>{{ $loan->user->name }}</td>
                    <td>{{ $loan->user->department?->name ?? '-' }}</td>
                    <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                    <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                    <td>{{ $loan->returned_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        @if ($loan->status === 'terlambat')
                            <strong>Terlambat</strong>
                            <span class="block text-gray-500">{{ (int) $loan->due_date->diffInDays(today()) }} hari</span>
                        @else
                            {{ \App\Models\Loan::STATUS_LABELS[$loan->status] }}
                        @endif
                    </td>
                    <td>{{ $loan->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-4 text-center text-gray-500">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-print-layout>
