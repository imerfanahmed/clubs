<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SyncPackagesToStripe extends Command
{
    protected $signature = 'packages:sync-stripe';

    protected $description = 'Sync all active packages to Stripe products and prices';

    public function handle(StripeClient $stripe): void
    {
        $packages = Package::where('is_active', true)->get();

        foreach ($packages as $package) {
            $this->info("Syncing: {$package->name}");

            if ($package->stripe_product_id) {
                $product = $stripe->products->update($package->stripe_product_id, [
                    'name' => $package->name,
                    'description' => $package->description,
                ]);
            } else {
                $product = $stripe->products->create([
                    'name' => $package->name,
                    'description' => $package->description,
                ]);
            }

            if ($package->stripe_price_id) {
                $stripe->prices->update($package->stripe_price_id, [
                    'active' => $package->is_active,
                ]);
            } else {
                $price = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $package->price,
                    'currency' => 'gbp',
                    'recurring' => ['interval' => $package->interval],
                ]);

                $package->update([
                    'stripe_product_id' => $product->id,
                    'stripe_price_id' => $price->id,
                ]);
            }

            $this->info("  -> {$package->name} synced.");
        }

        $this->info('All packages synced.');
    }
}
