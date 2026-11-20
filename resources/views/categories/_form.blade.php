<div class="mb-4">
    <label class="block text-sm font-medium mb-1">Nama Kategori</label>
    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}"
           class="w-full border-gray-300 rounded-md">
    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1">Prefix Kode Aset</label>
    <input type="text" name="code_prefix" maxlength="6" placeholder="mis. LPT"
           value="{{ old('code_prefix', $category->code_prefix ?? '') }}"
           class="w-full border-gray-300 rounded-md uppercase">
    <p class="mt-1 text-xs text-gray-500">
        2 sampai 6 huruf atau angka, dipakai untuk kode otomatis seperti LPT-001.
        Kosongkan untuk memakai AST.
    </p>
    @error('code_prefix') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1">Deskripsi</label>
    <textarea name="description" rows="3"
              class="w-full border-gray-300 rounded-md">{{ old('description', $category->description ?? '') }}</textarea>
    @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>
