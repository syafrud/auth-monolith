<?php

namespace Modules\Library\Catalog\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Shared\ValueObjects\Isbn;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('isbn'))) {
            $this->merge([
                'isbn' => strtoupper(str_replace(['-', ' '], '', $this->input('isbn'))),
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
            'isbn' => [
                'bail',
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! Isbn::isValid($value)) {
                        $fail('Format ISBN tidak valid.');
                    }
                },
                'unique:books,isbn',
            ],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'published_year' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
            'stock' => ['required', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:book_categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'ISBN sudah digunakan oleh buku lain.',
            'author_id.required' => 'Penulis wajib dipilih.',
            'author_id.exists' => 'Penulis yang dipilih tidak ditemukan.',
            'publisher_id.exists' => 'Penerbit yang dipilih tidak ditemukan.',
            'published_year.required' => 'Tahun terbit wajib diisi.',
            'published_year.min' => 'Tahun terbit minimal 1900.',
            'published_year.max' => 'Tahun terbit tidak boleh melebihi tahun sekarang.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak ditemukan.',
        ];
    }
}
