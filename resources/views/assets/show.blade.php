<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Detail Aset</h2></x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Banner untuk aset yang sudah dihapus --}}
        @if ($asset->trashed())
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
                <span>Aset ini sudah dihapus pada {{ $asset->deleted_at->format('d M Y H:i') }}.</span>
                <form method="POST" action="{{ route('assets.restore', $asset) }}"
                      data-confirm="Aset {{ $asset->name }} ({{ $asset->asset_code }}) akan dikembalikan ke daftar aset."
                      data-confirm-title="Pulihkan aset?"
                      data-confirm-button="Ya, pulihkan"
                      data-confirm-tone="primary">
                    @csrf
                    @method('PATCH')
                    <button class="font-semibold underline">Pulihkan</button>
                </form>
            </div>
        @endif

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
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

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <a href="{{ $asset->trashed() ? route('assets.trashed') : route('assets.index') }}"
                   class="text-indigo-600">&larr; Kembali</a>

                @if ($asset->status === 'tersedia' && ! $asset->trashed())
                    <a href="{{ route('loans.create', ['asset_id' => $asset->id]) }}"
                       class="rounded bg-green-600 px-4 py-2 text-white">Pinjam Aset Ini</a>
                @endif
            </div>
        </div>

        {{-- Riwayat peminjaman (admin) --}}
        @if (auth()->user()->isAdmin())
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-3 font-semibold text-indigo-900">Riwayat Peminjaman</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-indigo-50 text-indigo-900">
                            <tr>
                                <th class="p-2">Peminjam</th>
                                <th class="p-2">Tgl. Pinjam</th>
                                <th class="p-2">Jatuh Tempo</th>
                                <th class="p-2">Tgl. Kembali</th>
                                <th class="p-2">Status</th>
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
                                <td class="p-2">{{ $loan->user->name }}</td>
                                <td class="p-2">{{ $loan->loan_date->format('d M Y') }}</td>
                                <td class="p-2">{{ $loan->due_date->format('d M Y') }}</td>
                                <td class="p-2">{{ $loan->returned_at?->format('d M Y') ?? '-' }}</td>
                                <td class="p-2">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $badge }}">
                                        {{ \App\Models\Loan::STATUS_LABELS[$loan->status] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-2 text-gray-500">Aset ini belum pernah dipinjam.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($loans->count() === 20)
                    <p class="mt-2 text-xs text-gray-500">Menampilkan 20 peminjaman terakhir.</p>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
