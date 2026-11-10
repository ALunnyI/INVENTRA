<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Edit Kategori</h2></x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white p-6 shadow rounded">
            @csrf
            @method('PUT')
            @include('categories._form')
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
            <a href="{{ route('categories.index') }}" class="ml-2 text-gray-600">Batal</a>
        </form>
    </div>
</x-app-layout>
