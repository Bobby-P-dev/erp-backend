<?php

namespace Database\Factories\Core;

use App\Models\Core\AccountingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingCategory>
 */
class AccountingCategoryFactory extends Factory
{
    protected $model = AccountingCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CAT-'.fake()->unique()->numerify('###'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
