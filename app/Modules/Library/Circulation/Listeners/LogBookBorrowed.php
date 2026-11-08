<?php

namespace Modules\Library\Circulation\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Library\Circulation\Events\BookBorrowed;

class LogBookBorrowed
{
    public function handle(BookBorrowed $event): void
    {
        $loan = $event->loan;

        Log::info("Peminjaman {$loan->loan_code} berhasil dicatat.", [
            'member' => $loan->member_name,
            'books' => $loan->items->pluck('book_title')->all(),
            'due_at' => $loan->due_at->toDateString(),
        ]);
    }
}
