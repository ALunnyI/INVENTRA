<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Aset Terhapus</h2></x-slot>

    <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <div class="mb-4 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
            Aset di sini hanya disembunyikan dari daftar utama. Datanya dan riwayat peminjamannya
            masih tersimpan, dan kode asetnya masih dianggap terpakai sampai aset dihapus permanen.
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama atau kode aset..." class="border-gray-300 rounded-md">
            </form>
            <a href="{{ route('assets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke daftar aset
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded text-left">
                <thead class="bg-indigo-50 text-indigo-900">
                    <tr>
                        <th class="p-3">Kode</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Dihapus pada</th>
                        <th class="p-3">Riwayat</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($assets as $asset)
                    <tr class="border-t">
                        <td class="p-3">{{ $asset->asset_code }}</td>
                        <td class="p-3">{{ $asset->name }}</td>
                        <td class="p-3">{{ $asset->category->name }}</td>
                        <td class="p-3">{{ $asset->deleted_at->format('d M Y H:i') }}</td>
                        <td class="p-3">{{ $asset->loans_count }} peminjaman</td>
                        <td class="p-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('assets.show', $asset) }}" class="text-gray-700">Detail</a>

                                <form method="POST" action="{{ route('assets.restore', $asset) }}"
                                      data-confirm="Aset {{ $asset->name }} ({{ $asset->asset_code }}) akan dikembalikan ke daftar aset."
                                      data-confirm-title="Pulihkan aset?"
                                      data-confirm-button="Ya, pulihkan"
                                      data-confirm-tone="primary">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-indigo-600">Pulihkan</button>
                                </form>

                                @if ($asset->loans_count === 0)
                                    <form method="POST" action="{{ route('assets.force-destroy', $asset) }}"
                                          data-confirm="Aset {{ $asset->name }} ({{ $asset->asset_code }}) akan dihapus permanen beserta fotonya. Tindakan ini tidak dapat dibatalkan."
                                          data-confirm-title="Hapus permanen?"
                                          data-confirm-button="Ya, hapus permanen">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600">Hapus Permanen</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Punya riwayat</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-3 text-center text-gray-500">Tidak ada aset yang dihapus.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $assets->links() }}</div>
    </div>
</x-app-layout>
