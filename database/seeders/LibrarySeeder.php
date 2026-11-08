<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Library\Catalog\Models\Author;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Catalog\Models\BookCategory;
use Modules\Library\Catalog\Models\Publisher;
use Modules\Library\Circulation\Services\LoanService;
use Modules\Library\Membership\Models\Member;
use Modules\Library\Membership\Models\MemberRole;

class LibrarySeeder extends Seeder
{
    public function run(LoanService $loanService): void
    {
        // Membership Context
        $student = MemberRole::create(['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'max_loans' => 3, 'loan_days' => 7, 'fine_per_day' => 1000]);
        $lecturer = MemberRole::create(['name' => 'Dosen', 'slug' => 'dosen', 'max_loans' => 5, 'loan_days' => 14, 'fine_per_day' => 500]);

        $budi = Member::factory()->for($student, 'role')->create(['member_code' => 'AGT-00001', 'name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $siti = Member::factory()->for($lecturer, 'role')->create(['member_code' => 'AGT-00002', 'name' => 'Siti Aminah', 'email' => 'siti@example.com']);
        Member::factory()->for($student, 'role')->suspended()->create(['member_code' => 'AGT-00003', 'name' => 'Rudi Hartono', 'email' => 'rudi@example.com']);
        Member::factory()->for($student, 'role')->expired()->create(['member_code' => 'AGT-00004', 'name' => 'Dewi Lestari', 'email' => 'dewi@example.com']);

        // Catalog Context
        $andrea = Author::create(['name' => 'Andrea Hirata']);
        $tere = Author::create(['name' => 'Tere Liye']);
        $pram = Author::create(['name' => 'Pramoedya Ananta Toer']);

        $bentang = Publisher::create(['name' => 'Bentang Pustaka', 'city' => 'Yogyakarta']);
        $gramedia = Publisher::create(['name' => 'Gramedia Pustaka Utama', 'city' => 'Jakarta']);
        $lentera = Publisher::create(['name' => 'Lentera Dipantara', 'city' => 'Jakarta']);

        $fiction = BookCategory::create(['name' => 'Fiksi', 'slug' => 'fiksi']);
        $novel = $fiction->children()->create(['name' => 'Novel', 'slug' => 'novel']);
        $fiction->children()->create(['name' => 'Cerpen', 'slug' => 'cerpen']);
        $nonFiction = BookCategory::create(['name' => 'Nonfiksi', 'slug' => 'nonfiksi']);
        $history = $nonFiction->children()->create(['name' => 'Sejarah', 'slug' => 'sejarah']);
        $nonFiction->children()->create(['name' => 'Biografi', 'slug' => 'biografi']);

        $books = [
            ['Laskar Pelangi', '978-979-3062-79-2', $andrea, $bentang, 2005, 5, [$novel]],
            ['Sang Pemimpi', '978-979-3062-78-5', $andrea, $bentang, 2006, 0, [$novel]],
            ['Bumi', '978-602-03-0064-1', $tere, $gramedia, 2014, 3, [$novel]],
            ['Hujan', '978-602-03-4655-7', $tere, $gramedia, 2016, 4, [$novel]],
            ['Bumi Manusia', '978-979-97330-1-6', $pram, $lentera, 1980, 2, [$novel, $history]],
        ];

        foreach ($books as [$title, $isbn, $author, $publisher, $year, $stock, $categories]) {
            $book = Book::create([
                'title' => $title,
                'isbn' => $isbn,
                'author_id' => $author->id,
                'publisher_id' => $publisher->id,
                'published_year' => $year,
                'stock' => $stock,
            ]);

            $book->categories()->attach(collect($categories)->pluck('id'), ['added_at' => now()]);
        }

        // Circulation Context: satu peminjaman terlambat (denda berjalan) dan satu sudah dikembalikan.
        $loanService->borrow($budi->id, [Book::where('title', 'Bumi')->value('id')], now()->subDays(10));

        $returnedLoan = $loanService->borrow($siti->id, [Book::where('title', 'Hujan')->value('id')], now()->subDays(20));
        $loanService->returnLoan($returnedLoan, now());
    }
}
