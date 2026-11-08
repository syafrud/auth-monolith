<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Circulation\Events\BookBorrowed;
use Modules\Library\Circulation\Exceptions\LoanRejectedException;
use Modules\Library\Circulation\Models\Loan;
use Modules\Library\Circulation\Models\LoanItem;
use Modules\Library\Circulation\Services\LoanService;
use Modules\Library\Membership\Models\Member;
use Modules\Library\Membership\Models\MemberRole;

beforeEach(function () {
    $this->role = MemberRole::factory()->create(['max_loans' => 3, 'loan_days' => 7, 'fine_per_day' => 1000]);
    $this->member = Member::factory()->for($this->role, 'role')->create(['name' => 'Budi Santoso']);
});

test('borrowing books creates the loan, snapshots books, reduces stock and updates the member', function () {
    Event::fake([BookBorrowed::class]);
    $bumi = Book::factory()->create(['title' => 'Bumi', 'isbn' => '978-602-03-0064-1', 'stock' => 3]);
    $hujan = Book::factory()->create(['title' => 'Hujan', 'stock' => 1]);

    $loan = app(LoanService::class)->borrow($this->member->id, [$bumi->id, $hujan->id]);

    expect($loan->items)->toHaveCount(2)
        ->and($loan->items->first()->book_title)->toBe('Bumi')
        ->and($loan->items->first()->book_isbn)->toBe('9786020300641')
        ->and($loan->member_name)->toBe('Budi Santoso')
        ->and($loan->due_at->toDateString())->toBe(now()->addDays(7)->toDateString())
        ->and($bumi->fresh()->stock)->toBe(2)
        ->and($hujan->fresh()->stock)->toBe(0)
        ->and($this->member->fresh()->active_loans)->toBe(2)
        ->and($this->member->fresh()->status)->toBe(Member::STATUS_BORROWING);

    Event::assertDispatched(BookBorrowed::class, fn (BookBorrowed $event) => $event->loan->is($loan));
});

test('the whole loan is rolled back when one book is out of stock', function () {
    Event::fake([BookBorrowed::class]);
    $available = Book::factory()->create(['stock' => 2]);
    $empty = Book::factory()->outOfStock()->create(['title' => 'Sang Pemimpi']);

    expect(fn () => app(LoanService::class)->borrow($this->member->id, [$available->id, $empty->id]))
        ->toThrow(LoanRejectedException::class, "Stok buku 'Sang Pemimpi' tidak mencukupi");

    expect(Loan::withTrashed()->count())->toBe(0)
        ->and(LoanItem::count())->toBe(0)
        ->and($available->fresh()->stock)->toBe(2)
        ->and($this->member->fresh()->active_loans)->toBe(0)
        ->and($this->member->fresh()->status)->toBe(Member::STATUS_ACTIVE);

    Event::assertNotDispatched(BookBorrowed::class);
});

test('members who cannot borrow are rejected', function (Closure $makeMember, string $message) {
    $member = $makeMember($this->role);
    $book = Book::factory()->create(['stock' => 5]);

    expect(fn () => app(LoanService::class)->borrow($member->id, [$book->id]))
        ->toThrow(LoanRejectedException::class, $message);

    expect(Loan::count())->toBe(0)->and($book->fresh()->stock)->toBe(5);
})->with([
    'suspended' => [fn (MemberRole $role) => Member::factory()->for($role, 'role')->suspended()->create(), 'dibekukan'],
    'expired' => [fn (MemberRole $role) => Member::factory()->for($role, 'role')->expired()->create(), 'sudah berakhir'],
    'limit reached' => [fn (MemberRole $role) => Member::factory()->for($role, 'role')->create(['active_loans' => 3]), 'batas pinjam 3 buku'],
]);

test('the book borrowed listener writes a log entry after the transaction commits', function () {
    $book = Book::factory()->create(['title' => 'Laskar Pelangi', 'stock' => 1]);

    Log::shouldReceive('info')
        ->once()
        ->withArgs(fn (string $message, array $context) => str_contains($message, 'berhasil dicatat')
            && $context['member'] === 'Budi Santoso'
            && $context['books'] === ['Laskar Pelangi']);

    app(LoanService::class)->borrow($this->member->id, [$book->id]);
});

test('returning a late loan calculates the fine, restores stock and member status', function () {
    $book = Book::factory()->create(['stock' => 1]);
    $loanService = app(LoanService::class);
    $loan = $loanService->borrow($this->member->id, [$book->id], now()->subDays(10));

    $returned = $loanService->returnLoan($loan);

    expect($returned->status)->toBe(Loan::STATUS_RETURNED)
        ->and((float) $returned->fine_amount)->toBe(3000.0)
        ->and($book->fresh()->stock)->toBe(1)
        ->and($this->member->fresh()->active_loans)->toBe(0)
        ->and($this->member->fresh()->status)->toBe(Member::STATUS_ACTIVE);
});

test('loan form borrows books and shows a success message', function () {
    $book = Book::factory()->create(['title' => 'Bumi', 'stock' => 2]);

    $this->actingAs(User::factory()->create())
        ->post(route('library.loans.store'), ['member_id' => $this->member->id, 'book_ids' => [$book->id]])
        ->assertRedirect(route('library.loans.index'))
        ->assertSessionHas('success');

    expect($book->fresh()->stock)->toBe(1);
});

test('loan form shows the rollback error and keeps the input', function () {
    $book = Book::factory()->outOfStock()->create(['title' => 'Sang Pemimpi']);

    $this->actingAs(User::factory()->create())
        ->from(route('library.loans.create'))
        ->post(route('library.loans.store'), ['member_id' => $this->member->id, 'book_ids' => [$book->id]])
        ->assertRedirect(route('library.loans.create'))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Sang Pemimpi'))
        ->assertSessionHasInput('member_id', $this->member->id);

    expect(Loan::count())->toBe(0);
});

test('returning a loan from the list shows the fine', function () {
    $book = Book::factory()->create(['stock' => 1]);
    $loan = app(LoanService::class)->borrow($this->member->id, [$book->id], now()->subDays(9));

    $this->actingAs(User::factory()->create())
        ->patch(route('library.loans.return', $loan))
        ->assertRedirect(route('library.loans.index'))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'Denda: Rp 2.000'));
});
