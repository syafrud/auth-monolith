@php($isAdmin = auth()->user()->role === 'admin')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $showTrashed ? 'Buku Terhapus' : 'Katalog Buku' }}
            </h2>
            @if ($isAdmin)
                <div class="flex gap-3">
                    <a href="{{ route('library.books.index', $showTrashed ? [] : ['trashed' => 1]) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $showTrashed ? 'Kembali ke Katalog' : 'Lihat Terhapus' }}
                    </a>
                    <a href="{{ route('library.books.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        + Tambah Buku
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            @unless ($showTrashed)
                <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5">
                    <div class="min-w-[12rem] flex-1">
                        <label for="q" class="block text-sm font-medium text-gray-700">Cari judul, ISBN, atau penulis</label>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category" id="category" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                                @foreach ($category->children as $child)
                                    <option value="{{ $child->id }}" @selected(request('category') == $child->id)>&nbsp;&nbsp;— {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 pb-2 text-sm text-gray-700">
                        <input type="checkbox" name="available" value="1" @checked(request()->boolean('available')) class="rounded border-gray-300">
                        Hanya yang tersedia
                    </label>
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
                </form>
            @endunless

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">ISBN</th>
                            <th class="px-4 py-3 text-left">Penulis</th>
                            <th class="px-4 py-3 text-left">Penerbit</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($books as $book)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $book->title }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $book->isbn }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $book->author->name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $book->publisher?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($book->categories as $category)
                                            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $category->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $book->stock > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $book->stock }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-3">
                                        @if ($showTrashed)
                                            <form action="{{ route('library.books.restore', $book) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Pulihkan</button>
                                            </form>
                                        @else
                                            <a href="{{ route('library.books.show', $book) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Detail</a>
                                            @if ($isAdmin)
                                                <form action="{{ route('library.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Hapus buku ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada buku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $books->links() }}</div>
        </div>
    </div>
</x-app-layout>
