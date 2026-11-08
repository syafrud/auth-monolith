<?php

namespace Modules\Library\Circulation\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Library\Circulation\Models\Loan;

/**
 * Ditembakkan setelah transaction peminjaman commit, sehingga tidak muncul bila terjadi rollback.
 */
class BookBorrowed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Loan $loan
    ) {}
}
