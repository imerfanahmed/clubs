<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        Package::create([
            'name' => 'Student',
            'slug' => 'student',
            'description' => 'Discounted membership for students',
            'price' => 1500,
            'interval' => 'month',
            'sort_order' => 1,
        ]);

        Package::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Standard club membership',
            'price' => 2500,
            'interval' => 'month',
            'sort_order' => 2,
        ]);

        Package::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Premium membership with all benefits',
            'price' => 5000,
            'interval' => 'month',
            'sort_order' => 3,
        ]);
    }
}
