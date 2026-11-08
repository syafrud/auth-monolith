<?php

namespace Modules\Library\Circulation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Library\Circulation\ValueObjects\Fine;
use Modules\Shared\ValueObjects\Money;

class Loan extends Model
{
    use SoftDeletes;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'loan_code',
        'member_id',
        'member_name',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'fine_per_day',
        'fine_amount',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'fine_per_day' => 'decimal:2',
        'fine_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BORROWED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->active()->where('due_at', '<', now());
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    /**
     * Denda berjalan: dihitung sampai hari ini bila buku belum dikembalikan.
     */
    public function currentFine(): Fine
    {
        return Fine::calculate(
            $this->due_at,
            $this->returned_at ?? now(),
            new Money((float) $this->fine_per_day),
        );
    }
}
