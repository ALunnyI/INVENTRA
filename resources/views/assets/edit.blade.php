<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Edit Aset</h2></x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data"
              class="bg-white p-6 shadow rounded">
            @csrf
            @method('PUT')
            @include('assets._form')
            <div class="mt-6">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                <a href="{{ route('assets.index') }}" class="ml-2 text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
