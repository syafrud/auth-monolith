<?php

namespace Modules\Library\Catalog\Services;

use Modules\Library\Catalog\Models\Book;
use Modules\Shared\Contracts\BookCatalogInterface;

class BookCatalogService implements BookCatalogInterface
{
    public function getBookForLoan(int $bookId): ?array
    {
        $book = Book::find($bookId);

        if (! $book) {
            return null;
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'stock' => $book->stock,
        ];
    }

    public function getAvailableBooks(): array
    {
        return Book::available()
            ->orderBy('title')
            ->get(['id', 'title', 'stock'])
            ->map(fn(Book $book): array => [
                'id' => $book->id,
                'title' => $book->title,
                'stock' => $book->stock,
            ])
            ->all();
    }

    /**
     * Syarat stok ikut di query agar pengecekan dan pengurangan terjadi dalam satu perintah.
     */
    public function decreaseStock(int $bookId, int $quantity): bool
    {
        return Book::whereKey($bookId)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity) > 0;
    }

    public function increaseStock(int $bookId, int $quantity): void
    {
        Book::withTrashed()->whereKey($bookId)->increment('stock', $quantity);
    }
}
