<?php

namespace Modules\Library\Membership\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BORROWING = 'borrowing';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'member_role_id',
        'member_code',
        'name',
        'email',
        'phone',
        'status',
        'active_loans',
        'last_borrowed_at',
        'membership_expires_at',
    ];

    protected $casts = [
        'active_loans' => 'integer',
        'last_borrowed_at' => 'datetime',
        'membership_expires_at' => 'date',
    ];

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(MemberRole::class, 'member_role_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_SUSPENDED)
            ->whereDate('membership_expires_at', '>=', today());
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('member_code', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    public function isMembershipActive(): bool
    {
        return $this->membership_expires_at->endOfDay()->isFuture();
    }
}
