<?php

namespace Modules\Library\Membership\Models;

use Database\Factories\MemberRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'max_loans', 'loan_days', 'fine_per_day'];

    protected $casts = [
        'max_loans' => 'integer',
        'loan_days' => 'integer',
        'fine_per_day' => 'decimal:2',
    ];

    protected static function newFactory(): MemberRoleFactory
    {
        return MemberRoleFactory::new();
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
