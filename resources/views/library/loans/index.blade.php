@php($isAdmin = auth()->user()->role === 'admin')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $showTrashed ? 'Peminjaman Terhapus' : 'Peminjaman' }}
            </h2>
            <div class="flex gap-3">
                @if ($isAdmin)
                    <a href="{{ route('library.loans.index', $showTrashed ? [] : ['trashed' => 1]) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $showTrashed ? 'Kembali ke Daftar' : 'Lihat Terhapus' }}
                    </a>
                @endif
                <a href="{{ route('library.loans.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    + Pinjam Buku
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            @unless ($showTrashed)
                <div class="mb-4 flex gap-2 text-sm">
                    @foreach (['' => 'Semua', 'active' => 'Sedang dipinjam', 'overdue' => 'Terlambat'] as $value => $label)
                        <a href="{{ route('library.loans.index', array_filter(['status' => $value])) }}"
                           class="rounded-full px-3 py-1 font-medium {{ request('status', '') === $value ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endunless

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Anggota</th>
                            <th class="px-4 py-3 text-left">Buku (snapshot)</th>
                            <th class="px-4 py-3 text-left">Pinjam / Jatuh Tempo</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Denda</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($loans as $loan)
                            @php($fine = $loan->currentFine())
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $loan->loan_code }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $loan->member_name }}</td>
                                <td class="px-4 py-3">
                                    <ul class="flex flex-col gap-1">
                                        @foreach ($loan->items as $item)
                                            <li class="text-gray-900">{{ $item->book_title }} <span class="font-mono text-xs text-gray-500">{{ $item->book_isbn }}</span></li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $loan->borrowed_at->format('d-m-Y') }}
                                    <span class="block text-xs text-gray-500">s.d. {{ $loan->due_at->format('d-m-Y') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($loan->isReturned())
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">dikembalikan {{ $loan->returned_at->format('d-m-Y') }}</span>
                                    @elseif ($fine->isCharged())
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">terlambat {{ $fine->getDaysLate() }} hari</span>
                                    @else
                                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700">dipinjam</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-medium {{ $fine->isCharged() ? 'text-red-700' : 'text-gray-500' }}">
                                    {{ $fine }}
                                    @unless ($loan->isReturned())
                                        <span class="block text-xs font-normal text-gray-500">berjalan</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-3">
                                        @if ($showTrashed)
                                            <form action="{{ route('library.loans.restore', $loan) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Pulihkan</button>
                                            </form>
                                        @else
                                            @unless ($loan->isReturned())
                                                <form action="{{ route('library.loans.return', $loan) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-semibold text-indigo-600 hover:text-indigo-800">Kembalikan</button>
                                                </form>
                                            @endunless
                                            @if ($isAdmin)
                                                <form action="{{ route('library.loans.destroy', $loan) }}" method="POST" onsubmit="return confirm('Hapus data peminjaman ini?');">
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
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $loans->links() }}</div>
        </div>
    </div>
</x-app-layout>
