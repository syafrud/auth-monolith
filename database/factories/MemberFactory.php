<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Library\Membership\Models\Member;
use Modules\Library\Membership\Models\MemberRole;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'member_role_id' => MemberRole::factory(),
            'member_code' => 'AGT-'.fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'status' => Member::STATUS_ACTIVE,
            'active_loans' => 0,
            'membership_expires_at' => now()->addYear()->toDateString(),
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => Member::STATUS_SUSPENDED]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => ['membership_expires_at' => now()->subDay()->toDateString()]);
    }
}
