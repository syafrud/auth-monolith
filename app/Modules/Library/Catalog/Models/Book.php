<?php

namespace Modules\Library\Catalog\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\ValueObjects\Isbn;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'publisher_id',
        'published_year',
        'stock',
    ];

    protected $casts = [
        'published_year' => 'integer',
        'stock' => 'integer',
    ];

    protected static function newFactory(): BookFactory
    {
        return BookFactory::new();
    }

    /**
     * ISBN selalu melewati value object Isbn sebelum disimpan.
     */
    protected function isbn(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => (new Isbn($value))->getValue(),
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BookCategory::class, 'book_book_category')
            ->withPivot('added_at');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('isbn', 'like', '%'.str_replace('-', '', $keyword).'%')
                ->orWhereHas('author', fn (Builder $author) => $author->where('name', 'like', "%{$keyword}%"));
        });
    }

    /**
     * Filter kategori sekaligus sub-kategorinya.
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $category) use ($categoryId) {
            $category->where('book_categories.id', $categoryId)
                ->orWhere('book_categories.parent_id', $categoryId);
        });
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }
}
