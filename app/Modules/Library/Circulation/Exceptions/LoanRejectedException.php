<?php

namespace Modules\Library\Circulation\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Peminjaman ditolak aturan bisnis. Dilempar di dalam transaction sehingga memicu rollback.
 */
class LoanRejectedException extends Exception
{
    public function __construct(string $message, private string $reason)
    {
        parent::__construct($message);
    }

    public static function bookOutOfStock(string $title, int $stock): self
    {
        return new self("Stok buku '{$title}' tidak mencukupi (tersisa {$stock}). Peminjaman dibatalkan.", 'book_out_of_stock');
    }

    public static function bookNotFound(int $bookId): self
    {
        return new self("Buku dengan ID {$bookId} tidak ditemukan.", 'book_not_found');
    }

    public static function memberNotFound(int $memberId): self
    {
        return new self("Anggota dengan ID {$memberId} tidak ditemukan.", 'member_not_found');
    }

    public static function memberCannotBorrow(string $name, string $why): self
    {
        return new self("Anggota '{$name}' tidak dapat meminjam: {$why}.", 'member_cannot_borrow');
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function report(): void
    {
        Log::warning($this->getMessage(), ['reason' => $this->reason]);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => $this->reason,
            ], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}
