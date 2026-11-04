<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Library\Models\Author;
use Modules\Library\Models\Book;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validBookData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Laskar Pelangi',
        'isbn' => '9789793062792',
        'published_year' => 2005,
        'price' => 85000,
        'stock' => 5,
        'author_id' => Author::factory()->create()->id,
    ], $overrides);
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests are redirected to login from library pages', function () {
    auth()->logout();

    $this->get('/books')->assertRedirect(route('login', absolute: false));
    $this->get('/authors')->assertRedirect(route('login', absolute: false));
});

test('book and author pages can be rendered', function () {
    $book = Book::factory()->create();

    $this->get('/books')->assertOk()->assertSee($book->title);
    $this->get('/books/create')->assertOk();
    $this->get("/books/{$book->id}/edit")->assertOk()->assertSee($book->isbn);
    $this->get('/authors')->assertOk()->assertSee($book->author->name);
    $this->get('/authors/create')->assertOk();
    $this->get("/authors/{$book->author_id}/edit")->assertOk();
});

test('empty book form fails validation with indonesian messages', function () {
    $this->from('/books/create')
        ->post('/books', [])
        ->assertRedirect('/books/create')
        ->assertSessionHasErrors([
            'title' => 'Judul buku wajib diisi.',
            'isbn' => 'ISBN wajib diisi.',
            'published_year' => 'Tahun terbit wajib diisi.',
            'price' => 'Harga wajib diisi.',
            'stock' => 'Stok wajib diisi.',
            'author_id' => 'Penulis wajib dipilih.',
        ]);

    expect(Book::count())->toBe(0);
});

test('validation errors are shown on the book form', function () {
    $this->followingRedirects()
        ->from('/books/create')
        ->post('/books', [])
        ->assertOk()
        ->assertSee('Judul buku wajib diisi.');
});

test('invalid isbn is rejected', function (string $isbn) {
    $this->post('/books', validBookData(['isbn' => $isbn]))
        ->assertSessionHasErrors(['isbn' => 'Format ISBN tidak valid.']);
})->with(['abc', '12345', '9789793062791', '0306406153']);

test('isbn 10 and hyphenated isbn 13 are accepted and normalized', function (string $isbn, string $stored) {
    $this->post('/books', validBookData(['isbn' => $isbn]))->assertSessionHasNoErrors();

    expect(Book::where('isbn', $stored)->exists())->toBeTrue();
})->with([
    ['978-979-3062-79-2', '9789793062792'],
    ['0-306-40615-2', '0306406152'],
    ['080442957x', '080442957X'],
]);

test('duplicate isbn is rejected even when written with hyphens', function () {
    Book::factory()->create(['isbn' => '9789793062792']);

    $this->post('/books', validBookData(['isbn' => '978-979-3062-79-2']))
        ->assertSessionHasErrors(['isbn' => 'ISBN sudah digunakan oleh buku lain.']);
});

test('published year must be between 1900 and the current year', function () {
    $this->post('/books', validBookData(['published_year' => 1899]))
        ->assertSessionHasErrors(['published_year' => 'Tahun terbit minimal 1900.']);

    $this->post('/books', validBookData(['published_year' => (int) date('Y') + 1]))
        ->assertSessionHasErrors(['published_year' => 'Tahun terbit tidak boleh melebihi tahun sekarang.']);

    $this->post('/books', validBookData(['published_year' => (int) date('Y')]))
        ->assertSessionHasNoErrors();
});

test('negative price, negative stock and unknown author are rejected', function () {
    $this->post('/books', validBookData([
        'price' => -1,
        'stock' => -1,
        'author_id' => 999,
    ]))->assertSessionHasErrors([
        'price' => 'Harga tidak boleh negatif.',
        'stock' => 'Stok tidak boleh negatif.',
        'author_id' => 'Penulis yang dipilih tidak ditemukan.',
    ]);
});

test('valid book is stored with a success message', function () {
    $this->post('/books', validBookData())
        ->assertRedirect(route('books.index'))
        ->assertSessionHas('success', 'Buku berhasil ditambahkan.');

    expect(Book::where('isbn', '9789793062792')->exists())->toBeTrue();
});

test('book can be updated while keeping its own isbn', function () {
    $book = Book::factory()->create(['isbn' => '9789793062792']);

    $this->put("/books/{$book->id}", validBookData(['title' => 'Judul Baru']))
        ->assertRedirect(route('books.index'))
        ->assertSessionHasNoErrors();

    expect($book->fresh()->title)->toBe('Judul Baru');
});

test('book can not be updated to the isbn of another book', function () {
    Book::factory()->create(['isbn' => '9789793062792']);
    $book = Book::factory()->create(['isbn' => '0306406152']);

    $this->put("/books/{$book->id}", validBookData())
        ->assertSessionHasErrors('isbn');
});

test('book can be deleted', function () {
    $book = Book::factory()->create();

    $this->delete("/books/{$book->id}")
        ->assertRedirect(route('books.index'))
        ->assertSessionHas('success', 'Buku berhasil dihapus.');

    expect(Book::find($book->id))->toBeNull();
});

test('empty author form fails validation with indonesian messages', function () {
    $this->from('/authors/create')
        ->post('/authors', [])
        ->assertRedirect('/authors/create')
        ->assertSessionHasErrors([
            'name' => 'Nama penulis wajib diisi.',
            'email' => 'Email wajib diisi.',
        ]);
});

test('invalid email, duplicate email and long bio are rejected', function () {
    Author::factory()->create(['email' => 'andrea@example.com']);

    $this->post('/authors', ['name' => 'A', 'email' => 'bukan-email'])
        ->assertSessionHasErrors(['email' => 'Format email tidak valid.']);

    $this->post('/authors', ['name' => 'A', 'email' => 'andrea@example.com'])
        ->assertSessionHasErrors(['email' => 'Email sudah digunakan oleh penulis lain.']);

    $this->post('/authors', ['name' => 'A', 'email' => 'a@example.com', 'bio' => str_repeat('a', 1001)])
        ->assertSessionHasErrors(['bio' => 'Bio maksimal 1000 karakter.']);
});

test('valid author is stored and bio is optional', function () {
    $this->post('/authors', ['name' => 'Tere Liye', 'email' => 'tere@example.com'])
        ->assertRedirect(route('authors.index'))
        ->assertSessionHas('success', 'Penulis berhasil ditambahkan.');

    expect(Author::where('email', 'tere@example.com')->exists())->toBeTrue();
});

test('author can be updated while keeping their own email', function () {
    $author = Author::factory()->create(['email' => 'tere@example.com']);

    $this->put("/authors/{$author->id}", ['name' => 'Nama Baru', 'email' => 'tere@example.com'])
        ->assertRedirect(route('authors.index'))
        ->assertSessionHasNoErrors();

    expect($author->fresh()->name)->toBe('Nama Baru');
});

test('borrowing an available book decrements its stock', function () {
    $book = Book::factory()->create(['title' => 'Bumi', 'stock' => 2]);

    $this->post("/books/{$book->id}/borrow")
        ->assertRedirect(route('books.index'))
        ->assertSessionHas('success', "Buku 'Bumi' berhasil dipinjam.");

    expect($book->fresh()->stock)->toBe(1);
});

test('borrowing a book with no stock shows an error and logs it', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with("Buku 'Sang Pemimpi' tidak dapat dipinjam karena stok habis.", ['book' => 'Sang Pemimpi']);

    $book = Book::factory()->outOfStock()->create(['title' => 'Sang Pemimpi']);

    $this->from('/books')
        ->post("/books/{$book->id}/borrow")
        ->assertRedirect('/books')
        ->assertSessionHas('error', "Buku 'Sang Pemimpi' tidak dapat dipinjam karena stok habis.");

    expect($book->fresh()->stock)->toBe(0);
});

test('book not available error is displayed on the book list', function () {
    $book = Book::factory()->outOfStock()->create(['title' => 'Sang Pemimpi']);

    $this->followingRedirects()
        ->from('/books')
        ->post("/books/{$book->id}/borrow")
        ->assertOk()
        ->assertSee('tidak dapat dipinjam karena stok habis.');
});

test('book not available error returns json for api requests', function () {
    $book = Book::factory()->outOfStock()->create(['title' => 'Sang Pemimpi']);

    $this->postJson("/books/{$book->id}/borrow")
        ->assertStatus(422)
        ->assertJson([
            'error' => 'book_not_available',
            'details' => ['book' => 'Sang Pemimpi', 'stock' => 0],
        ]);
});

test('author with books can not be deleted', function () {
    Log::shouldReceive('warning')->once();

    $author = Author::factory()->hasBooks(2)->create(['name' => 'Andrea Hirata']);

    $this->from('/authors')
        ->delete("/authors/{$author->id}")
        ->assertRedirect('/authors')
        ->assertSessionHas('error', "Penulis 'Andrea Hirata' tidak dapat dihapus karena masih memiliki 2 buku.");

    expect(Author::find($author->id))->not->toBeNull()
        ->and($author->books()->count())->toBe(2);
});

test('author has books error returns json for api requests', function () {
    $author = Author::factory()->hasBooks(1)->create(['name' => 'Andrea Hirata']);

    $this->deleteJson("/authors/{$author->id}")
        ->assertStatus(409)
        ->assertJson([
            'error' => 'author_has_books',
            'details' => ['author' => 'Andrea Hirata', 'books_count' => 1],
        ]);
});

test('author without books can be deleted', function () {
    $author = Author::factory()->create();

    $this->delete("/authors/{$author->id}")
        ->assertRedirect(route('authors.index'))
        ->assertSessionHas('success', 'Penulis berhasil dihapus.');

    expect(Author::find($author->id))->toBeNull();
});
