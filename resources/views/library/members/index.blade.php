@php($isAdmin = auth()->user()->role === 'admin')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $showTrashed ? 'Anggota Terhapus' : 'Daftar Anggota' }}
            </h2>
            @if ($isAdmin)
                <a href="{{ route('library.members.index', $showTrashed ? [] : ['trashed' => 1]) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    {{ $showTrashed ? 'Kembali ke Daftar' : 'Lihat Terhapus' }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            @unless ($showTrashed)
                <form method="GET" class="mb-4 flex gap-3">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kode, atau email" class="block w-full max-w-sm rounded-md border-gray-300 shadow-sm">
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Cari</button>
                </form>
            @endunless

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Sedang Dipinjam</th>
                            <th class="px-4 py-3 text-left">Berlaku Sampai</th>
                            @if ($isAdmin)
                                <th class="px-4 py-3 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($members as $member)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $member->member_code }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $member->role->name }}
                                    <span class="block text-xs text-gray-500">maks {{ $member->role->max_loans }} buku · {{ $member->role->loan_days }} hari</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php($statusClass = ['active' => 'bg-green-50 text-green-700', 'borrowing' => 'bg-sky-50 text-sky-700', 'suspended' => 'bg-red-50 text-red-700'][$member->status] ?? 'bg-gray-50 text-gray-700')
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $member->status }}</span>
                                    @unless ($member->isMembershipActive())
                                        <span class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">kedaluwarsa</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $member->active_loans }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $member->membership_expires_at->format('d-m-Y') }}</td>
                                @if ($isAdmin)
                                    <td class="px-4 py-3 text-center">
                                        @if ($showTrashed)
                                            <form action="{{ route('library.members.restore', $member) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Pulihkan</button>
                                            </form>
                                        @else
                                            <form action="{{ route('library.members.destroy', $member) }}" method="POST" onsubmit="return confirm('Hapus anggota ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada anggota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $members->links() }}</div>
        </div>
    </div>
</x-app-layout>
