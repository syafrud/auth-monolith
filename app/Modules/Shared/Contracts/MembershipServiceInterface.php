<?php

namespace Modules\Shared\Contracts;

interface MembershipServiceInterface
{
    /**
     * @return array{id: int, name: string, status: string, active_loans: int, max_loans: int, loan_days: int, fine_per_day: float, membership_active: bool}|null
     */
    public function getMemberForLoan(int $memberId): ?array;

    /**
     * @return list<array{id: int, member_code: string, name: string}>
     */
    public function getActiveMembers(): array;

    public function recordBorrowing(int $memberId, int $bookCount): void;

    public function recordReturn(int $memberId, int $bookCount): void;
}
