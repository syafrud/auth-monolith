<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Library\Models\Author;
use Modules\Library\Models\Book;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $andrea = Author::factory()->create([
            'name' => 'Andrea Hirata',
            'email' => 'andrea@example.com',
        ]);
        $tere = Author::factory()->create([
            'name' => 'Tere Liye',
            'email' => 'tere@example.com',
        ]);
        Author::factory()->create([
            'name' => 'Penulis Baru',
            'email' => 'penulis.baru@example.com',
        ]);

        Book::factory()->for($andrea)->create([
            'title' => 'Laskar Pelangi',
            'published_year' => 2005,
            'price' => 85000,
            'stock' => 5,
        ]);
        Book::factory()->for($andrea)->outOfStock()->create([
            'title' => 'Sang Pemimpi',
            'published_year' => 2006,
            'price' => 75000,
        ]);
        Book::factory()->for($tere)->create([
            'title' => 'Bumi',
            'published_year' => 2014,
            'price' => 95000,
            'stock' => 3,
        ]);
    }
}
