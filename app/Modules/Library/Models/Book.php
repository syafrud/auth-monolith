<?php

namespace Modules\Library\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(BookFactory::class)]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'published_year',
        'price',
        'stock',
        'author_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
