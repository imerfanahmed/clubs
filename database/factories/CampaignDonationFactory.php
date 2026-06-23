<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignDonation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignDonation>
 */
class CampaignDonationFactory extends Factory
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
            'user_id' => null,
            'type' => CampaignDonation::TYPE_MONEY,
            'amount' => fake()->numberBetween(500, 50000),
            'currency' => 'GBP',
            'pledge_item_id' => null,
            'pledge_quantity' => null,
            'payment_method' => CampaignDonation::METHOD_OFFLINE,
            'status' => CampaignDonation::STATUS_PENDING,
            'donor_name' => fake()->name(),
            'donor_email' => fake()->safeEmail(),
            'donor_phone' => null,
            'message' => null,
            'is_anonymous' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CampaignDonation::STATUS_COMPLETED,
            'approved_at' => now(),
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_method' => CampaignDonation::METHOD_CARD,
        ]);
    }

    public function pledge(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CampaignDonation::TYPE_PLEDGE,
            'amount' => null,
            'payment_method' => null,
            'pledge_quantity' => fake()->numberBetween(10, 500),
        ]);
    }
}
