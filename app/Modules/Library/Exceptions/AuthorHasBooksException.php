<?php

namespace Modules\Library\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthorHasBooksException extends Exception
{
    public function __construct(protected string $authorName, protected int $booksCount)
    {
        parent::__construct("Penulis '{$authorName}' tidak dapat dihapus karena masih memiliki {$booksCount} buku.");
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getBooksCount(): int
    {
        return $this->booksCount;
    }

    public function report(): void
    {
        Log::warning($this->getMessage(), [
            'author' => $this->authorName,
            'books_count' => $this->booksCount,
        ]);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'author_has_books',
                'details' => [
                    'author' => $this->authorName,
                    'books_count' => $this->booksCount,
                ],
            ], 409);
        }

        return back()->with('error', $this->getMessage());
    }
}
