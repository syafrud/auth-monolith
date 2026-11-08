<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Library\Catalog\Models\Author;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Catalog\Models\Publisher;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->words(3, true)),
            'isbn' => fake()->unique()->isbn13(),
            'author_id' => Author::factory(),
            'publisher_id' => Publisher::factory(),
            'published_year' => fake()->numberBetween(1990, (int) date('Y')),
            'stock' => fake()->numberBetween(1, 10),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn(): array => ['stock' => 0]);
    }
}
