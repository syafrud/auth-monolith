<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Buku</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            <form action="{{ route('library.books.store') }}" method="POST" class="flex flex-col gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN (10 atau 13 digit, boleh memakai tanda hubung)</label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('isbn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="author_id" class="block text-sm font-medium text-gray-700">Penulis</label>
                        <select name="author_id" id="author_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih Penulis --</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" @selected(old('author_id') == $author->id)>{{ $author->name }}</option>
                            @endforeach
                        </select>
                        @error('author_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="publisher_id" class="block text-sm font-medium text-gray-700">Penerbit</label>
                        <select name="publisher_id" id="publisher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih Penerbit --</option>
                            @foreach ($publishers as $publisher)
                                <option value="{{ $publisher->id }}" @selected(old('publisher_id') == $publisher->id)>{{ $publisher->name }}</option>
                            @endforeach
                        </select>
                        @error('publisher_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="published_year" class="block text-sm font-medium text-gray-700">Tahun Terbit</label>
                        <input type="number" name="published_year" id="published_year" value="{{ old('published_year') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('published_year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stok</label>
                        <input type="number" name="stock" id="stock" min="0" value="{{ old('stock') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Kategori</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        @foreach ($categories as $category)
                            @foreach (collect([$category])->merge($category->children) as $option)
                                <label class="flex items-center gap-2 {{ $option->parent_id ? 'pl-5' : 'font-medium' }}">
                                    <input type="checkbox" name="category_ids[]" value="{{ $option->id }}" @checked(in_array($option->id, old('category_ids', []))) class="rounded border-gray-300">
                                    {{ $option->name }}
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex items-center gap-4">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('library.books.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
