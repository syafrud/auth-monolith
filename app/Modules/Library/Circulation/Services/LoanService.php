<?php

namespace Modules\Library\Circulation\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Library\Circulation\Events\BookBorrowed;
use Modules\Library\Circulation\Exceptions\LoanRejectedException;
use Modules\Library\Circulation\Models\Loan;
use Modules\Library\Circulation\ValueObjects\Fine;
use Modules\Shared\Contracts\BookCatalogInterface;
use Modules\Shared\Contracts\MembershipServiceInterface;
use Modules\Shared\ValueObjects\Money;

class LoanService
{
    public function __construct(
        private BookCatalogInterface $bookCatalog,
        private MembershipServiceInterface $membership,
    ) {}

    /**
     * Membuat peminjaman secara atomik: record peminjaman, kurangi stok, update status anggota.
     *
     * @param  list<int>  $bookIds
     */
    public function borrow(int $memberId, array $bookIds, ?CarbonInterface $borrowedAt = null): Loan
    {
        $bookIds = array_values(array_unique($bookIds));
        $borrowedAt ??= now();

        return DB::transaction(function () use ($memberId, $bookIds, $borrowedAt) {
            $member = $this->membership->getMemberForLoan($memberId);
            $this->ensureMemberCanBorrow($memberId, $member, count($bookIds));

            $loan = Loan::create([
                'loan_code' => 'PJM-'.$borrowedAt->format('Ymd').'-'.strtoupper(Str::random(5)),
                'member_id' => $member['id'],
                'member_name' => $member['name'],
                'borrowed_at' => $borrowedAt,
                'due_at' => $borrowedAt->copy()->addDays($member['loan_days']),
                'status' => Loan::STATUS_BORROWED,
                'fine_per_day' => $member['fine_per_day'],
            ]);

            foreach ($bookIds as $bookId) {
                $book = $this->bookCatalog->getBookForLoan($bookId);

                if ($book === null) {
                    throw LoanRejectedException::bookNotFound($bookId);
                }

                // Snapshot judul & ISBN agar riwayat tidak berubah bila data buku diubah.
                $loan->items()->create([
                    'book_id' => $book['id'],
                    'book_title' => $book['title'],
                    'book_isbn' => $book['isbn'],
                    'quantity' => 1,
                ]);

                if (! $this->bookCatalog->decreaseStock($book['id'], 1)) {
                    throw LoanRejectedException::bookOutOfStock($book['title'], $book['stock']);
                }
            }

            $this->membership->recordBorrowing($member['id'], count($bookIds));

            event(new BookBorrowed($loan->load('items')));

            return $loan;
        });
    }

    /**
     * Pengembalian: hitung denda otomatis, kembalikan stok, dan update status anggota.
     */
    public function returnLoan(Loan $loan, ?CarbonInterface $returnedAt = null): Loan
    {
        $returnedAt ??= now();

        return DB::transaction(function () use ($loan, $returnedAt) {
            $loan->loadMissing('items');
            $fine = Fine::calculate($loan->due_at, $returnedAt, new Money((float) $loan->fine_per_day));

            foreach ($loan->items as $item) {
                $this->bookCatalog->increaseStock($item->book_id, $item->quantity);
            }

            $this->membership->recordReturn($loan->member_id, $loan->items->sum('quantity'));

            $loan->update([
                'returned_at' => $returnedAt,
                'status' => Loan::STATUS_RETURNED,
                'fine_amount' => $fine->getAmount()->getAmount(),
            ]);

            return $loan;
        });
    }

    /**
     * @param  array{id: int, name: string, status: string, active_loans: int, max_loans: int, loan_days: int, fine_per_day: float, membership_active: bool}|null  $member
     */
    private function ensureMemberCanBorrow(int $memberId, ?array $member, int $bookCount): void
    {
        if ($member === null) {
            throw LoanRejectedException::memberNotFound($memberId);
        }

        if ($member['status'] === 'suspended') {
            throw LoanRejectedException::memberCannotBorrow($member['name'], 'keanggotaan sedang dibekukan');
        }

        if (! $member['membership_active']) {
            throw LoanRejectedException::memberCannotBorrow($member['name'], 'masa keanggotaan sudah berakhir');
        }

        if ($member['max_loans'] < $member['active_loans'] + $bookCount) {
            throw LoanRejectedException::memberCannotBorrow(
                $member['name'],
                "batas pinjam {$member['max_loans']} buku, sedang meminjam {$member['active_loans']}"
            );
        }
    }
}
