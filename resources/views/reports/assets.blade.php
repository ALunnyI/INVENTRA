<x-print-layout title="Laporan Status Aset" :filters="$filters"
                :excel-url="route('reports.assets.xlsx', request()->query())">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-3 text-center text-sm sm:grid-cols-6">
        <div class="rounded border border-gray-300 p-2">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold">{{ $assets->count() }}</p>
        </div>
        @foreach (\App\Models\Asset::STATUSES as $key => $label)
            <div class="rounded border border-gray-300 p-2">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-xl font-bold">{{ $byStatus->get($key, 0) }}</p>
            </div>
        @endforeach
        @foreach (\App\Models\Asset::CONDITIONS as $key => $label)
            @if ($key !== 'baik')
                <div class="rounded border border-gray-300 p-2">
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="text-xl font-bold">{{ $byCondition->get($key, 0) }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <p class="mt-2 text-xs text-gray-500">
        Kondisi baik: {{ $byCondition->get('baik', 0) }} aset.
    </p>

    {{-- Tabel --}}
    <table class="report mt-4">
        <thead>
            <tr>
                <th class="w-8">No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Merek</th>
                <th>No. Seri</th>
                <th>Tgl. Beli</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $asset)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $asset->asset_code }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category->name }}</td>
                    <td>{{ $asset->brand ?: '-' }}</td>
                    <td>{{ $asset->serial_number ?: '-' }}</td>
                    <td>{{ $asset->purchase_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ \App\Models\Asset::CONDITIONS[$asset->condition] }}</td>
                    <td>{{ \App\Models\Asset::STATUSES[$asset->status] }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-4 text-center text-gray-500">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-print-layout>
