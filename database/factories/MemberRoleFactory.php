<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Library\Membership\Models\MemberRole;

class MemberRoleFactory extends Factory
{
    protected $model = MemberRole::class;

    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'max_loans' => 3,
            'loan_days' => 7,
            'fine_per_day' => 1000,
        ];
    }
}
