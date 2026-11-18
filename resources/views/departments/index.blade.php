<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Departemen</h2></x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex justify-between mb-4">
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari departemen..." class="border-gray-300 rounded-md">
            </form>
            <a href="{{ route('departments.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">+ Tambah</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded text-left">
                <thead class="bg-indigo-50 text-indigo-900">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Anggota</th>
                        <th class="p-3">Aset Dipinjam</th>
                        <th class="p-3">Terlambat</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($departments as $department)
                    @php
                        $stat    = $loanStats->get($department->id);
                        $active  = (int) ($stat->active_total ?? 0);
                        $overdue = (int) ($stat->overdue_total ?? 0);
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">
                            <a href="{{ route('departments.show', $department) }}"
                               class="font-medium text-indigo-700 hover:text-indigo-900">
                                {{ $department->name }}
                            </a>
                        </td>
                        <td class="p-3">{{ $department->users_count }}</td>
                        <td class="p-3">{{ $active }}</td>
                        <td class="p-3">
                            @if ($overdue > 0)
                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                    {{ $overdue }} terlambat
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="flex gap-3">
                                <a href="{{ route('departments.show', $department) }}" class="text-gray-700">Detail</a>
                                <a href="{{ route('departments.edit', $department) }}" class="text-indigo-600">Edit</a>
                                <form method="POST" action="{{ route('departments.destroy', $department) }}"
                                      data-confirm="Departemen {{ $department->name }} akan dihapus. Tindakan ini tidak dapat dibatalkan."
                                      data-confirm-title="Hapus departemen?"
                                      data-confirm-button="Ya, hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-3 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $departments->links() }}</div>
    </div>
</x-app-layout>
