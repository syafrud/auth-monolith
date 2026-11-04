<x-validation-summary />

<div class="mb-4">
    <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
    <input type="text" name="name" id="name" value="{{ old('name', $author?->name) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    @error('name')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
    <input type="text" name="email" id="email" value="{{ old('email', $author?->email) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
    <textarea name="bio" id="bio" rows="4"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('bio', $author?->bio) }}</textarea>
    @error('bio')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    Simpan
</button>
