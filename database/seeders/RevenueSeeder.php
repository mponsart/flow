<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $revenues = [
            [
                'client_id' => 1,
                'subscription_id' => 1,
                'amount' => 29.00,
                'date' => now()->subMonths(1),
                'type' => 'mensuel',
                'note' => 'Paiement mensuel VPS Starter.'
            ],
            [
                'client_id' => 2,
                'subscription_id' => 2,
                'amount' => 299.00,
                'date' => now()->subMonths(2),
                'type' => 'annuel',
                'note' => 'Paiement annuel VPS Pro.'
            ],
            [
                'client_id' => 4,
                'subscription_id' => 4,
                'amount' => 29.00,
                'date' => now()->subMonths(1),
                'type' => 'mensuel',
                'note' => 'Paiement mensuel VPS Starter.'
            ],
            [
                'client_id' => 5,
                'subscription_id' => 5,
                'amount' => 299.00,
                'date' => now()->subMonths(3),
                'type' => 'annuel',
                'note' => 'Paiement annuel VPS Pro.'
            ],
        ];
        foreach ($revenues as $rev) {
            \App\Models\Revenue::create($rev);
        }
    }
}
