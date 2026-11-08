@php
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">

            {{-- Sapaan --}}
            <section class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-8 text-white shadow-sm sm:px-10">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                        <h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Halo, {{ $user->name }}!</h1>
                        <p class="mt-2 max-w-xl text-sm text-indigo-100">
                            Berikut ringkasan aktivitas belanja kamu. Jelajahi katalog untuk menemukan produk terbaru.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                            Lihat Produk
                        </a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            Profil Saya
                        </a>
                        @if ($isAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                Panel Admin
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Statistik --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan">
                <x-stat-card label="Pesanan Saya" :value="number_format($salesSummary['total_orders'], 0, ',', '.')" hint="Semua pesanan yang pernah dibuat" color="indigo">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Menunggu Proses" :value="number_format($salesSummary['pending_orders'], 0, ',', '.')" hint="Pesanan berstatus pending" color="amber">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Total Belanja" :value="'Rp ' . number_format($salesSummary['total_revenue'], 0, ',', '.')" hint="Tidak termasuk pesanan dibatalkan" color="emerald">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </x-slot>
                </x-stat-card>

                <x-stat-card label="Produk Tersedia" :value="number_format($catalogSummary['available_products'], 0, ',', '.')" :hint="$catalogSummary['total_categories'] . ' kategori di katalog'" color="sky">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    </x-slot>
                </x-stat-card>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Pesanan terbaru --}}
                <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 lg:col-span-2">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Pesanan Terbaru</h3>
                            <p class="text-sm text-gray-500">5 pesanan terakhir milik kamu</p>
                        </div>
                    </div>
                    <x-orders-table :orders="$recentOrders" />
                </section>

                {{-- Info akun --}}
                <section class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Akun Saya</h3>
                    </div>
                    <div class="flex flex-1 flex-col gap-5 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-lg font-semibold text-indigo-700" aria-hidden="true">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="truncate text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Peran</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $isAdmin ? 'bg-violet-50 text-violet-700 ring-violet-600/20' : 'bg-gray-50 text-gray-700 ring-gray-500/20' }}">
                                        {{ $isAdmin ? 'Admin' : 'User' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Bergabung</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $user->created_at?->locale('id')->translatedFormat('d M Y') }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('profile.edit') }}" class="mt-auto inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            Kelola Profil
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
