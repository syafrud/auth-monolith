<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Pinjam Buku</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <x-flash-messages />

            <form action="{{ route('library.loans.store') }}" method="POST" class="flex flex-col gap-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                @csrf

                <div>
                    <label for="member_id" class="block text-sm font-medium text-gray-700">Anggota</label>
                    <select name="member_id" id="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach ($members as $member)
                            <option value="{{ $member['id'] }}" @selected(old('member_id') == $member['id'])>{{ $member['member_code'] }} — {{ $member['name'] }}</option>
                        @endforeach
                    </select>
                    @error('member_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Buku yang tersedia (maks. 5)</legend>
                    <div class="mt-2 flex flex-col gap-2 text-sm">
                        @forelse ($books as $book)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="book_ids[]" value="{{ $book['id'] }}" @checked(in_array($book['id'], old('book_ids', []))) class="rounded border-gray-300">
                                {{ $book['title'] }} <span class="text-xs text-gray-500">(stok {{ $book['stock'] }})</span>
                            </label>
                        @empty
                            <p class="text-gray-500">Tidak ada buku yang tersedia.</p>
                        @endforelse
                    </div>
                    @error('book_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </fieldset>

                <div class="flex items-center gap-4">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Pinjam</button>
                    <a href="{{ route('library.loans.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
