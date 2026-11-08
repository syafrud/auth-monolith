<?php

namespace Modules\Library\Membership\Services;

use Modules\Library\Membership\Models\Member;
use Modules\Shared\Contracts\MembershipServiceInterface;

class MembershipService implements MembershipServiceInterface
{
    public function getMemberForLoan(int $memberId): ?array
    {
        $member = Member::with('role')->find($memberId);

        if (! $member) {
            return null;
        }

        return [
            'id' => $member->id,
            'name' => $member->name,
            'status' => $member->status,
            'active_loans' => $member->active_loans,
            'max_loans' => $member->role->max_loans,
            'loan_days' => $member->role->loan_days,
            'fine_per_day' => (float) $member->role->fine_per_day,
            'membership_active' => $member->isMembershipActive(),
        ];
    }

    public function getActiveMembers(): array
    {
        return Member::active()
            ->orderBy('name')
            ->get(['id', 'member_code', 'name'])
            ->map(fn (Member $member): array => [
                'id' => $member->id,
                'member_code' => $member->member_code,
                'name' => $member->name,
            ])
            ->all();
    }

    public function recordBorrowing(int $memberId, int $bookCount): void
    {
        $member = Member::lockForUpdate()->findOrFail($memberId);

        $member->update([
            'active_loans' => $member->active_loans + $bookCount,
            'status' => Member::STATUS_BORROWING,
            'last_borrowed_at' => now(),
        ]);
    }

    public function recordReturn(int $memberId, int $bookCount): void
    {
        $member = Member::withTrashed()->lockForUpdate()->findOrFail($memberId);
        $activeLoans = max(0, $member->active_loans - $bookCount);

        $member->update([
            'active_loans' => $activeLoans,
            'status' => $activeLoans === 0 && $member->status === Member::STATUS_BORROWING
                ? Member::STATUS_ACTIVE
                : $member->status,
        ]);
    }
}
