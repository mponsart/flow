<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            [
                'client_id' => 1,
                'service_id' => 1,
                'category' => 'infrastructure',
                'amount' => 10.00,
                'date' => now()->subMonths(1),
                'note' => 'Coût VPS Starter.'
            ],
            [
                'client_id' => 2,
                'service_id' => 2,
                'category' => 'infrastructure',
                'amount' => 25.00,
                'date' => now()->subMonths(2),
                'note' => 'Coût VPS Pro.'
            ],
            [
                'client_id' => 4,
                'service_id' => 1,
                'category' => 'infrastructure',
                'amount' => 10.00,
                'date' => now()->subMonths(1),
                'note' => 'Coût VPS Starter.'
            ],
            [
                'client_id' => 5,
                'service_id' => 2,
                'category' => 'infrastructure',
                'amount' => 25.00,
                'date' => now()->subMonths(3),
                'note' => 'Coût VPS Pro.'
            ],
        ];
        foreach ($expenses as $exp) {
            \App\Models\Expense::create($exp);
        }
    }
}
