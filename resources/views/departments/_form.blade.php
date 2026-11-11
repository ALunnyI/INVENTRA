<div class="mb-4">
    <label class="block text-sm font-medium mb-1">Nama Department</label>
    <input type="text" name="name" value="{{ old('name', $department->name ?? '') }}"
           class="w-full border-gray-300 rounded-md">
    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>
