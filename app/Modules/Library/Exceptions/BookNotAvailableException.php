<?php

namespace Modules\Library\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookNotAvailableException extends Exception
{
    public function __construct(protected string $bookTitle)
    {
        parent::__construct("Buku '{$bookTitle}' tidak dapat dipinjam karena stok habis.");
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function report(): void
    {
        Log::warning($this->getMessage(), ['book' => $this->bookTitle]);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'book_not_available',
                'details' => [
                    'book' => $this->bookTitle,
                    'stock' => 0,
                ],
            ], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
