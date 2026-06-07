<?php

namespace Database\Seeders;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $expenses = [];

        // 12 months of varied expenses
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);

            // Infrastructure — monthly
            $expenses[] = ['amount' => 180.00, 'category' => 'Infrastructure', 'date' => $date->copy()->day(1)->format('Y-m-d'), 'description' => 'Serveurs OVH — facturation mensuelle'];
            $expenses[] = ['amount' => 45.00, 'category' => 'Infrastructure', 'date' => $date->copy()->day(5)->format('Y-m-d'), 'description' => 'Domaines & certificats SSL'];

            // Hébergement
            $expenses[] = ['amount' => 120.00, 'category' => 'Hébergement', 'date' => $date->copy()->day(3)->format('Y-m-d'), 'description' => 'Hébergement datacenter Roubaix'];

            // Personnel — monthly
            $expenses[] = ['amount' => 2800.00, 'category' => 'Personnel', 'date' => $date->copy()->day(28)->format('Y-m-d'), 'description' => 'Salaire chargé — technicien support'];

            // Marketing — quarterly
            if (in_array($month, [0, 3, 6, 9])) {
                $expenses[] = ['amount' => 350.00, 'category' => 'Marketing', 'date' => $date->copy()->day(15)->format('Y-m-d'), 'description' => 'Communication & newsletters trimestrielles'];
            }

            // Autres — occasional
            if ($month % 3 === 0) {
                $expenses[] = ['amount' => rand(50, 200), 'category' => 'Autre', 'date' => $date->copy()->day(rand(10, 25))->format('Y-m-d'), 'description' => 'Frais divers — matériel & fournitures'];
            }
        }

        foreach ($expenses as $e) {
            Expense::create($e);
        }
    }
}
