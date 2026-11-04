<?php

namespace Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Library\Rules\Isbn;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Samakan penulisan ISBN agar 978-602-... dan 978602... terbaca sebagai ISBN yang sama oleh aturan unique.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('isbn')) {
            $this->merge([
                'isbn' => strtoupper(str_replace(['-', ' '], '', (string) $this->input('isbn'))),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', new Isbn, 'unique:books,isbn'],
            'published_year' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'author_id' => ['required', 'exists:authors,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'ISBN sudah digunakan oleh buku lain.',
            'published_year.required' => 'Tahun terbit wajib diisi.',
            'published_year.integer' => 'Tahun terbit harus berupa bilangan bulat.',
            'published_year.min' => 'Tahun terbit minimal 1900.',
            'published_year.max' => 'Tahun terbit tidak boleh melebihi tahun sekarang.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'author_id.required' => 'Penulis wajib dipilih.',
            'author_id.exists' => 'Penulis yang dipilih tidak ditemukan.',
        ];
    }
}
