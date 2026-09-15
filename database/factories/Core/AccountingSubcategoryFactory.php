<?php

namespace Database\Factories\Core;

use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingSubcategory>
 */
class AccountingSubcategoryFactory extends Factory
{
    protected $model = AccountingSubcategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accounting_category_id' => AccountingCategory::factory(),
            'code' => 'SUB-'.fake()->unique()->numerify('###'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
