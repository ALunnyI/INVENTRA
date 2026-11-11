<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Detail Aset</h2></x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow rounded">
            @if ($asset->photo_url)
                <img src="{{ $asset->photo_url }}" alt="Foto aset" class="h-48 rounded mb-4">
            @endif

            <dl class="grid grid-cols-3 gap-y-2">
                <dt class="text-gray-500">Kode</dt><dd class="col-span-2">{{ $asset->asset_code }}</dd>
                <dt class="text-gray-500">Nama</dt><dd class="col-span-2">{{ $asset->name }}</dd>
                <dt class="text-gray-500">Kategori</dt><dd class="col-span-2">{{ $asset->category->name }}</dd>
                <dt class="text-gray-500">Merek</dt><dd class="col-span-2">{{ $asset->brand ?: '-' }}</dd>
                <dt class="text-gray-500">No. Seri</dt><dd class="col-span-2">{{ $asset->serial_number ?: '-' }}</dd>
                <dt class="text-gray-500">Tgl. Pembelian</dt>
                <dd class="col-span-2">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</dd>
                <dt class="text-gray-500">Kondisi</dt>
                <dd class="col-span-2">{{ \App\Models\Asset::CONDITIONS[$asset->condition] }}</dd>
                <dt class="text-gray-500">Status</dt>
                <dd class="col-span-2">{{ \App\Models\Asset::STATUSES[$asset->status] }}</dd>
                <dt class="text-gray-500">Catatan</dt><dd class="col-span-2">{{ $asset->notes ?: '-' }}</dd>
            </dl>

            <a href="{{ route('assets.index') }}" class="mt-6 inline-block text-indigo-600">&larr; Kembali</a>
        </div>
    </div>
</x-app-layout>
