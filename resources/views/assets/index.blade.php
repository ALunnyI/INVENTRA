<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Daftar Aset</h2></x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap justify-between gap-3 mb-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, kode, atau no. seri..." class="border-gray-300 rounded-md">

                <select name="category_id" class="border-gray-300 rounded-md">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="border-gray-300 rounded-md">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Asset::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button class="px-4 py-2 bg-gray-700 text-white rounded">Filter</button>
                <a href="{{ route('assets.index') }}" class="px-3 py-2 text-gray-600">Reset</a>
            </form>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('assets.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded">+ Tambah</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded text-left">
                <thead class="bg-indigo-50 text-indigo-900">
                    <tr>
                        <th class="p-3">Foto</th>
                        <th class="p-3">Kode</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Kondisi</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($assets as $asset)
                    @php
                        $badge = match ($asset->status) {
                            'tersedia' => 'bg-green-100 text-green-800',
                            'dipinjam' => 'bg-yellow-100 text-yellow-800',
                            default    => 'bg-gray-200 text-gray-800',
                        };
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">
                            @if ($asset->photo_url)
                                <img src="{{ $asset->photo_url }}" alt="" class="h-12 w-12 object-cover rounded">
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $asset->asset_code }}</td>
                        <td class="p-3">{{ $asset->name }}</td>
                        <td class="p-3">{{ $asset->category->name }}</td>
                        <td class="p-3">{{ \App\Models\Asset::CONDITIONS[$asset->condition] }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-sm {{ $badge }}">
                                {{ \App\Models\Asset::STATUSES[$asset->status] }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-3">
                                <a href="{{ route('assets.show', $asset) }}" class="text-gray-700">Detail</a>
                                @if ($asset->status === 'tersedia')
                                    <a href="{{ route('loans.create', ['asset_id' => $asset->id]) }}" class="text-green-700">Pinjam</a>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('assets.edit', $asset) }}" class="text-indigo-600">Edit</a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}"
                                        data-confirm="Aset {{ $asset->name }} ({{ $asset->asset_code }}) akan dihapus dari daftar. Riwayat peminjamannya tetap tersimpan."
                                        data-confirm-title="Hapus aset?"
                                        data-confirm-button="Ya, hapus">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-3 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $assets->links() }}</div>
    </div>
</x-app-layout>
