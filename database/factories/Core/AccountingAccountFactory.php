<?php

namespace Database\Factories\Core;

use App\Models\Core\AccountingAccount;
use App\Models\Core\AccountingSubcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingAccount>
 */
class AccountingAccountFactory extends Factory
{
    protected $model = AccountingAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accounting_subcategory_id' => AccountingSubcategory::factory(),
            'code' => 'ACC-'.fake()->unique()->numerify('####'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
