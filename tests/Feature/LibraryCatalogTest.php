<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Library\Catalog\Models\Author;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Catalog\Models\BookCategory;
use Modules\Library\Circulation\Models\Loan;
use Modules\Library\Circulation\Services\LoanService;
use Modules\Library\Membership\Models\Member;

test('book category pivot stores added_at and categories can have sub categories', function () {
    $fiction = BookCategory::factory()->create(['name' => 'Fiksi']);
    $novel = $fiction->children()->create(['name' => 'Novel', 'slug' => 'novel']);
    $book = Book::factory()->create();

    $book->categories()->attach($novel, ['added_at' => '2026-11-01 10:00:00']);

    $category = $book->categories()->first();

    expect($category->pivot->added_at)->toBe('2026-11-01 10:00:00')
        ->and($novel->parent->is($fiction))->toBeTrue()
        ->and($fiction->children->pluck('name')->all())->toBe(['Novel']);
});

test('book scopes filter available books, search keyword and category including sub categories', function () {
    $fiction = BookCategory::factory()->create();
    $novel = $fiction->children()->create(['name' => 'Novel', 'slug' => 'novel']);
    $andrea = Author::factory()->create(['name' => 'Andrea Hirata']);

    $laskar = Book::factory()->for($andrea)->create(['title' => 'Laskar Pelangi', 'stock' => 5]);
    $pemimpi = Book::factory()->for($andrea)->outOfStock()->create(['title' => 'Sang Pemimpi']);
    $other = Book::factory()->create(['title' => 'Atomic Habits', 'stock' => 1]);
    $laskar->categories()->attach($novel, ['added_at' => now()]);

    expect(Book::available()->pluck('title')->all())->toEqualCanonicalizing(['Laskar Pelangi', 'Atomic Habits'])
        ->and(Book::search('andrea')->pluck('title')->all())->toEqualCanonicalizing(['Laskar Pelangi', 'Sang Pemimpi'])
        ->and(Book::search($laskar->isbn)->pluck('id')->all())->toBe([$laskar->id])
        ->and(Book::inCategory($fiction->id)->pluck('id')->all())->toBe([$laskar->id])
        ->and(Book::inCategory($novel->id)->available()->pluck('id')->all())->toBe([$laskar->id]);
});

test('book list uses eager loading so the query count does not grow with the data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = BookCategory::factory()->create();

    $countQueries = function () use ($admin): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('library.books.index'))->assertOk();

        return count(DB::getQueryLog());
    };

    Book::factory()->count(2)->hasAttached($category, ['added_at' => now()], 'categories')->create();
    $queriesWithTwoBooks = $countQueries();

    Book::factory()->count(8)->hasAttached($category, ['added_at' => now()], 'categories')->create();
    $queriesWithTenBooks = $countQueries();

    expect($queriesWithTenBooks)->toBe($queriesWithTwoBooks);
});

test('loan list eager loads loan items', function () {
    $member = Member::factory()->create();
    $loanService = app(LoanService::class);
    $loanService->borrow($member->id, [Book::factory()->create()->id]);

    DB::enableQueryLog();
    Loan::with('items')->get()->each(fn (Loan $loan) => $loan->items->count());
    $eager = count(DB::getQueryLog());

    expect($eager)->toBe(2);
});

test('admin can add a book with a valid isbn and categories', function () {
    $author = Author::factory()->create();
    $novel = BookCategory::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('library.books.store'), [
            'title' => 'Laskar Pelangi',
            'isbn' => '978-979-3062-79-2',
            'author_id' => $author->id,
            'published_year' => 2005,
            'stock' => 5,
            'category_ids' => [$novel->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $book = Book::firstWhere('title', 'Laskar Pelangi');

    expect($book->isbn)->toBe('9789793062792')
        ->and($book->categories->pluck('id')->all())->toBe([$novel->id])
        ->and($book->categories->first()->pivot->added_at)->not->toBeNull();
});

test('book form rejects an invalid or duplicate isbn', function (string $isbn, string $message) {
    Book::factory()->create(['isbn' => '9789793062792']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('library.books.store'), [
            'title' => 'Buku Baru',
            'isbn' => $isbn,
            'author_id' => Author::factory()->create()->id,
            'published_year' => 2020,
            'stock' => 1,
        ])
        ->assertSessionHasErrors(['isbn' => $message]);
})->with([
    'invalid checksum' => ['9789793062791', 'Format ISBN tidak valid.'],
    'duplicate with hyphens' => ['978-979-3062-79-2', 'ISBN sudah digunakan oleh buku lain.'],
]);

test('library pages can be rendered', function (string $routeName) {
    Book::factory()->create();
    Member::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route($routeName))
        ->assertOk();
})->with(['library.books.index', 'library.books.create', 'library.members.index', 'library.loans.index', 'library.loans.create']);
