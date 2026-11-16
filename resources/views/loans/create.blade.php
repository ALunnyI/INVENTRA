<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Pinjam Aset</h2></x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('loans.store') }}" class="bg-white p-6 shadow rounded space-y-4">
            @csrf

            @if ($users->isNotEmpty())
                <div>
                    <x-input-label for="user_id" value="Peminjam" />
                    <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md">
                        <option value="">-- Diri sendiri --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                </div>
            @endif

            <div>
                <x-input-label for="asset_id" value="Aset" />
                <select id="asset_id" name="asset_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="">-- Pilih aset yang tersedia --</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}"
                            @selected(old('asset_id', $selectedAsset) == $asset->id)>
                            {{ $asset->asset_code }} - {{ $asset->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('asset_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="due_date" value="Tanggal Jatuh Tempo" />
                <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                              min="{{ today()->toDateString() }}"
                              max="{{ today()->addDays(\App\Models\Loan::MAX_DURATION_DAYS)->toDateString() }}"
                              :value="old('due_date', today()->addDays(7)->toDateString())" />
                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="notes" value="Catatan (opsional)" />
                <textarea id="notes" name="notes" rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>

            <p class="text-sm text-gray-500">
                Maksimal {{ \App\Models\Loan::MAX_DURATION_DAYS }} hari per peminjaman dan
                {{ \App\Models\Loan::MAX_ACTIVE_PER_USER }} peminjaman aktif per orang.
            </p>

            <div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                <a href="{{ route('loans.index') }}" class="ml-2 text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
