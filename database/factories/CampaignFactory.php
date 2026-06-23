<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'image_path' => null,
            'goal_amount' => fake()->numberBetween(50000, 5000000),
            'currency' => 'GBP',
            'status' => Campaign::STATUS_DRAFT,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Campaign::STATUS_ACTIVE,
        ]);
    }
}
