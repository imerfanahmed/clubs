<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignPledgeItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignPledgeItem>
 */
class CampaignPledgeItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->randomElement(['Bricks', 'Cement Bags', 'Prayer Mats', 'Roof Tiles']),
            'unit' => fake()->randomElement(['units', 'bags', 'items']),
            'target_quantity' => fake()->numberBetween(50, 5000),
            'sort_order' => 0,
        ];
    }
}
