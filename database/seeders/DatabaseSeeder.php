<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            ClientSeeder::class,
            ServiceSeeder::class,
            SubscriptionSeeder::class,
            RevenueSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
