<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = Campaign::create([
            'title' => 'New Wudu Area Renovation',
            'slug' => 'new-wudu-area-renovation',
            'summary' => 'Help us rebuild the wudu facilities for the community.',
            'description' => "Our masjid's wudu area is in urgent need of renovation. With your support we can install new sinks, flooring, and water-saving fixtures so that worshippers can perform wudu in comfort and dignity.\n\nEvery contribution, big or small, brings us closer to our goal. You may donate money or pledge materials.",
            'goal_amount' => 1500000,
            'currency' => 'GBP',
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $campaign->pledgeItems()->createMany([
            ['name' => 'Bricks', 'unit' => 'bricks', 'target_quantity' => 5000, 'sort_order' => 1],
            ['name' => 'Cement Bags', 'unit' => 'bags', 'target_quantity' => 200, 'sort_order' => 2],
            ['name' => 'Prayer Mats', 'unit' => 'mats', 'target_quantity' => 50, 'sort_order' => 3],
        ]);
    }
}
