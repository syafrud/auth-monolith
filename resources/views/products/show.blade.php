@php
    if ($product->stock <= 0) {
        $stockLabel = 'Stok habis';
        $stockClasses = 'bg-rose-50 text-rose-700 ring-rose-600/20';
    } elseif ($product->stock <= $product->low_stock_threshold) {
        $stockLabel = 'Stok menipis';
        $stockClasses = 'bg-amber-50 text-amber-700 ring-amber-600/20';
    } else {
        $stockLabel = 'Stok aman';
        $stockClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Detail Produk') }}
            </h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                &larr; Kembali ke daftar produk
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
                        <p class="mt-1 font-mono text-xs text-gray-500">{{ $product->slug }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $stockClasses }}">{{ $stockLabel }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $product->is_active ? 'bg-sky-50 text-sky-700 ring-sky-600/20' : 'bg-gray-50 text-gray-600 ring-gray-500/20' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-3">
                        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            Edit
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-px bg-gray-100 sm:grid-cols-3">
                    <div class="bg-white px-6 py-4">
                        <dt class="text-sm text-gray-500">Harga</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($product->price, 2, ',', '.') }}</dd>
                    </div>
                    <div class="bg-white px-6 py-4">
                        <dt class="text-sm text-gray-500">Stok</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($product->stock, 0, ',', '.') }}</dd>
                    </div>
                    <div class="bg-white px-6 py-4">
                        <dt class="text-sm text-gray-500">Batas stok menipis</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $product->low_stock_threshold }}</dd>
                    </div>
                </dl>

                <div class="flex flex-col gap-6 border-t border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Kategori</h3>
                        @if ($product->categories->isEmpty())
                            <p class="mt-2 text-sm text-gray-500">Belum ada kategori.</p>
                        @else
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach ($product->categories as $category)
                                    <li class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Deskripsi</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $product->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <p class="text-xs text-gray-400">
                        Dibuat {{ $product->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                        · Diperbarui {{ $product->updated_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
