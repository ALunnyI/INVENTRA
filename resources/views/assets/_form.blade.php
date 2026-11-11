@php $asset = $asset ?? new \App\Models\Asset; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="asset_code" value="Kode Aset" />
        <x-text-input id="asset_code" name="asset_code" type="text" class="mt-1 block w-full"
                      :value="old('asset_code', $asset->asset_code)" />
        <x-input-error :messages="$errors->get('asset_code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" value="Nama Aset" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $asset->name)" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category_id" value="Kategori" />
        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md">
            <option value="">-- Pilih kategori --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $asset->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="brand" value="Merek" />
        <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full"
                      :value="old('brand', $asset->brand)" />
        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="serial_number" value="Nomor Seri" />
        <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full"
                      :value="old('serial_number', $asset->serial_number)" />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="purchase_date" value="Tanggal Pembelian" />
        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full"
                      :value="old('purchase_date', $asset->purchase_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="condition" value="Kondisi" />
        <select id="condition" name="condition" class="mt-1 block w-full border-gray-300 rounded-md">
            @foreach (\App\Models\Asset::CONDITIONS as $value => $label)
                <option value="{{ $value }}" @selected(old('condition', $asset->condition ?? 'baik') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('condition')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        @if ($asset->status === 'dipinjam')
            <p class="mt-2 text-sm text-yellow-700">
                Sedang dipinjam. Status berubah otomatis saat aset dikembalikan.
            </p>
        @else
            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md">
                <option value="tersedia" @selected(old('status', $asset->status ?? 'tersedia') === 'tersedia')>Tersedia</option>
                <option value="maintenance" @selected(old('status', $asset->status) === 'maintenance')>Maintenance</option>
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        @endif
    </div>
</div>

<div class="mt-4">
    <x-input-label for="photo" value="Foto (maks. 2 MB)" />
    @if ($asset->photo_url)
        <img src="{{ $asset->photo_url }}" alt="Foto aset" class="h-24 w-24 object-cover rounded mt-2">
        <p class="text-xs text-gray-500">Pilih file baru hanya jika ingin mengganti foto.</p>
    @endif
    <input id="photo" name="photo" type="file" accept="image/*" class="mt-2 block w-full text-sm">
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="notes" value="Catatan" />
    <textarea id="notes" name="notes" rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md">{{ old('notes', $asset->notes) }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>
