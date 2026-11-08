@props(['orders', 'showCustomer' => false])

@php
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'processing' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'paid' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'shipped' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
    ];
@endphp

@if (count($orders) === 0)
    <div class="flex flex-col items-center gap-2 px-6 py-12 text-center">
        <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <p class="text-sm font-medium text-gray-900">Belum ada pesanan</p>
        <p class="text-sm text-gray-500">Pesanan yang dibuat akan tampil di sini.</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-left">No. Pesanan</th>
                    @if ($showCustomer)
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-left">Pelanggan</th>
                    @endif
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-center">Item</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-right">Total</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-left">Status</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-left">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-3 font-mono text-xs font-medium text-gray-900">{{ $order['order_number'] }}</td>
                        @if ($showCustomer)
                            <td class="whitespace-nowrap px-6 py-3 text-gray-700">{{ $order['customer_name'] }}</td>
                        @endif
                        <td class="whitespace-nowrap px-6 py-3 text-center text-gray-700">{{ $order['items_count'] }}</td>
                        <td class="whitespace-nowrap px-6 py-3 text-right font-medium text-gray-900">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusClasses[$order['status']] ?? 'bg-gray-50 text-gray-600 ring-gray-500/20' }}">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3 text-gray-500">{{ $order['ordered_at']?->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
