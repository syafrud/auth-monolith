<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">

            {{-- Sapaan --}}
            <section class="rounded-2xl bg-gradient-to-br from-slate-800 to-indigo-900 px-6 py-8 text-white shadow-sm sm:px-10">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-300">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                        <h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Panel Admin</h1>
                        <p class="mt-2 max-w-xl text-sm text-slate-300">
                            Pantau penjualan, stok katalog, dan pengguna dari satu tempat.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Produk
                        </a>
                        <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            Kelola Produk
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            Dashboard Saya
                        </a>
                    </div>
                </div>
            </section>

            {{-- Statistik --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan">
                <x-stat-card label="Pendapatan" :value="'Rp ' . number_format($salesSummary['total_revenue'], 0, ',', '.')" hint="Tidak termasuk pesanan dibatalkan" color="emerald">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Total Pesanan" :value="number_format($salesSummary['total_orders'], 0, ',', '.')" :hint="$salesSummary['pending_orders'] . ' menunggu proses'" color="indigo">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Produk" :value="number_format($catalogSummary['total_products'], 0, ',', '.')" :hint="$catalogSummary['available_products'] . ' tersedia · ' . $catalogSummary['total_categories'] . ' kategori'" color="sky">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Pengguna" :value="number_format($totalUsers, 0, ',', '.')" :hint="$totalAdmins . ' admin · ' . $totalRegularUsers . ' user'" color="amber">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    </x-slot>
                </x-stat-card>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Pesanan terbaru --}}
                <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 lg:col-span-2">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Pesanan Terbaru</h3>
                        <p class="text-sm text-gray-500">5 pesanan terakhir dari semua pelanggan</p>
                    </div>
                    <x-orders-table :orders="$recentOrders" show-customer />
                </section>

                {{-- Stok menipis --}}
                <section class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Stok Menipis</h3>
                            <p class="text-sm text-gray-500">
                                {{ $catalogSummary['low_stock_products'] }} menipis · {{ $catalogSummary['out_of_stock_products'] }} habis
                            </p>
                        </div>
                    </div>

                    @forelse ($lowStockProducts as $product)
                        @php
                            $stockPercent = $product['low_stock_threshold'] > 0
                                ? min(100, (int) round($product['stock'] / $product['low_stock_threshold'] * 100))
                                : 0;
                        @endphp
                        <div class="flex flex-col gap-2 border-b border-gray-100 px-6 py-3 last:border-b-0">
                            <div class="flex items-center justify-between gap-3">
                                <a href="{{ route('products.show', $product['id']) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 hover:underline">{{ $product['name'] }}</a>
                                @if ($product['stock'] <= 0)
                                    <span class="shrink-0 rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20">Habis</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Sisa {{ $product['stock'] }}</span>
                                @endif
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $product['stock'] }}" aria-valuemin="0" aria-valuemax="{{ $product['low_stock_threshold'] }}" aria-label="Stok {{ $product['name'] }}">
                                <div class="h-full rounded-full {{ $product['stock'] <= 0 ? 'bg-rose-500' : 'bg-amber-500' }}" style="width: {{ $stockPercent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-1 flex-col items-center justify-center gap-2 px-6 py-12 text-center">
                            <svg class="h-10 w-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-900">Semua stok aman</p>
                        </div>
                    @endforelse

                    <div class="mt-auto border-t border-gray-100 px-6 py-3">
                        <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                            Kelola produk &rarr;
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
