<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subs = [
            [
                'client_id' => 1,
                'service_id' => 1,
                'cycle' => 'mensuel',
                'start_date' => now()->subMonths(12),
                'end_date' => now()->addMonths(12),
                'status' => 'actif',
                'renewal' => true,
            ],
            [
                'client_id' => 2,
                'service_id' => 2,
                'cycle' => 'annuel',
                'start_date' => now()->subYears(1),
                'end_date' => now()->addYears(1),
                'status' => 'actif',
                'renewal' => true,
            ],
            [
                'client_id' => 3,
                'service_id' => 3,
                'cycle' => 'mensuel',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(6),
                'status' => 'inactif',
                'renewal' => false,
            ],
            [
                'client_id' => 4,
                'service_id' => 1,
                'cycle' => 'mensuel',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'status' => 'actif',
                'renewal' => true,
            ],
            [
                'client_id' => 5,
                'service_id' => 2,
                'cycle' => 'annuel',
                'start_date' => now()->subYears(2),
                'end_date' => now()->addYears(1),
                'status' => 'actif',
                'renewal' => true,
            ],
        ];
        foreach ($subs as $sub) {
            \App\Models\Subscription::create($sub);
        }
    }
}
