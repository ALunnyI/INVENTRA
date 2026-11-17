<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Department</h2></x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex justify-between mb-4">
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari department..." class="border-gray-300 rounded-md">
            </form>
            <a href="{{ route('departments.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">+ Tambah</a>
        </div>

        <table class="w-full bg-white shadow rounded text-left">
            <thead class="bg-indigo-50 text-indigo-900">
                <tr><th class="p-3">Nama</th><th class="p-3">Jumlah Karyawan</th><th class="p-3">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse ($departments as $department)
                <tr class="border-t">
                    <td class="p-3">{{ $department->name }}</td>
                    <td class="p-3">{{ $department->users_count }}</td>
                    <td class="p-3 flex gap-3">
                        <a href="{{ route('departments.edit', $department) }}" class="text-indigo-600">Edit</a>
                        <form method="POST" action="{{ route('departments.destroy', $department) }}"
                              onsubmit="return confirm('Hapus department ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="p-3 text-center text-gray-500">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $departments->links() }}</div>
    </div>
</x-app-layout>
