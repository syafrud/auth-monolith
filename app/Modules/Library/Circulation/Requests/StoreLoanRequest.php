<?php

namespace Modules\Library\Circulation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Keberadaan anggota & buku dicek LoanService melalui contract, bukan query tabel context lain.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer'],
            'book_ids' => ['required', 'array', 'min:1', 'max:5'],
            'book_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Anggota wajib dipilih.',
            'book_ids.required' => 'Pilih minimal satu buku.',
            'book_ids.min' => 'Pilih minimal satu buku.',
            'book_ids.max' => 'Maksimal 5 buku per peminjaman.',
        ];
    }
}
