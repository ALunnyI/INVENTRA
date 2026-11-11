<x-app-layout>
    <div class="py-12 max-w-2xl mx-auto text-center">
        <h1 class="text-4xl font-bold text-gray-700">403</h1>
        <p class="mt-2 text-gray-600">{{ $exception->getMessage() ?: 'Akses ditolak.' }}</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-indigo-600">Kembali ke Dashboard</a>
    </div>
</x-app-layout>
