<?php

namespace Database\Factories\Purchasing;

use App\Models\Purchasing\Item;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierItem>
 */
class SupplierItemFactory extends Factory
{
    protected $model = SupplierItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'item_id' => Item::factory(),
            'supplier_item_code' => 'SUP-ITM-'.fake()->unique()->numerify('####'),
            'supplier_item_name' => fake()->words(3, true),
            'reference_url' => fake()->url(),
            'default_price' => fake()->randomFloat(2, 1000, 1000000),
            'currency' => 'IDR',
            'minimum_order_quantity' => fake()->randomFloat(2, 1, 100),
            'lead_time_days' => fake()->numberBetween(1, 30),
            'is_active' => true,
        ];
    }
}
