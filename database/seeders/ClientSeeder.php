<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'AlphaTech',
                'company' => 'AlphaTech',
                'status' => 'actif',
                'notes' => 'Client historique premium.'
            ],
            [
                'name' => 'BetaCorp',
                'company' => 'BetaCorp',
                'status' => 'actif',
                'notes' => 'Client SaaS annuel.'
            ],
            [
                'name' => 'GammaServices',
                'company' => 'GammaServices',
                'status' => 'inactif',
                'notes' => 'Client inactif, à relancer.'
            ],
            [
                'name' => 'DeltaSolutions',
                'company' => 'DeltaSolutions',
                'status' => 'actif',
                'notes' => 'Client PME.'
            ],
            [
                'name' => 'EpsilonGroup',
                'company' => 'EpsilonGroup',
                'status' => 'actif',
                'notes' => 'Client à fort potentiel.'
            ],
        ];
        foreach ($clients as $client) {
            \App\Models\Client::create($client);
        }
    }
}
