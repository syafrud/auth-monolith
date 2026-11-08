<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Buku</h2>
            <a href="{{ route('library.books.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">&larr; Kembali ke katalog</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $book->title }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-500">ISBN {{ $book->isbn }}</p>

                <dl class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">Penulis</dt>
                        <dd class="font-medium text-gray-900">{{ $book->author->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Penerbit</dt>
                        <dd class="font-medium text-gray-900">{{ $book->publisher?->name ?? '-' }} {{ $book->publisher?->city ? '('.$book->publisher->city.')' : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tahun Terbit</dt>
                        <dd class="font-medium text-gray-900">{{ $book->published_year }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Stok</dt>
                        <dd class="font-medium {{ $book->isAvailable() ? 'text-green-700' : 'text-red-700' }}">
                            {{ $book->stock }} {{ $book->isAvailable() ? '(tersedia)' : '(habis)' }}
                        </dd>
                    </div>
                </dl>

                <h3 class="mt-6 text-sm font-semibold text-gray-900">Kategori</h3>
                <table class="mt-2 min-w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-2">Kategori</th>
                            <th class="py-2">Induk</th>
                            <th class="py-2">Ditambahkan (pivot added_at)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($book->categories as $category)
                            <tr>
                                <td class="py-2 text-gray-900">{{ $category->name }}</td>
                                <td class="py-2 text-gray-600">{{ $category->parent?->name ?? '-' }}</td>
                                <td class="py-2 text-gray-600">{{ $category->pivot->added_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-2 text-gray-500">Belum ada kategori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</x-app-layout>
