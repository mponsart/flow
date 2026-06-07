<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::pluck('id', 'name');
        $services = Service::pluck('id', 'name');

        $subscriptions = [
            [$clients['Marie Dupont'], $services['Hébergement Cloud Pro'], 'monthly', '2023-01-15', 'actif'],
            [$clients['Marie Dupont'], $services['CRM Associatif'], 'monthly', '2023-01-15', 'actif'],
            [$clients['Marie Dupont'], $services['Support Premium'], 'monthly', '2023-06-01', 'actif'],
            [$clients['Jean-Pierre Martin'], $services['Hébergement Cloud Starter'], 'monthly', '2023-03-01', 'actif'],
            [$clients['Jean-Pierre Martin'], $services['Sauvegarde Cloud'], 'monthly', '2023-03-01', 'actif'],
            [$clients['Sophie Bernard'], $services['Hébergement Cloud Pro'], 'monthly', '2023-04-15', 'actif'],
            [$clients['Sophie Bernard'], $services['CRM Associatif'], 'annual', '2024-01-01', 'actif'],
            [$clients['Thomas Leroy'], $services['Formation Numérique'], 'annual', '2024-02-01', 'actif'],
            [$clients['Thomas Leroy'], $services['Hébergement Cloud Starter'], 'monthly', '2023-08-01', 'actif'],
            [$clients['Claire Moreau'], $services['Hébergement Cloud Pro'], 'monthly', '2023-09-01', 'actif'],
            [$clients['Claire Moreau'], $services['Support Premium'], 'monthly', '2023-09-01', 'actif'],
            [$clients['Antoine Petit'], $services['CRM Associatif'], 'monthly', '2024-01-01', 'actif'],
            [$clients['Antoine Petit'], $services['Sauvegarde Cloud'], 'monthly', '2024-01-01', 'actif'],
            [$clients['Marc Gauthier'], $services['Hébergement Cloud Starter'], 'monthly', '2024-03-01', 'actif'],
            [$clients['Marc Gauthier'], $services['Formation Numérique'], 'annual', '2024-06-01', 'actif'],
        ];

        foreach ($subscriptions as [$clientId, $serviceId, $cycle, $startDate, $status]) {
            Subscription::create([
                'client_id' => $clientId,
                'service_id' => $serviceId,
                'cycle' => $cycle,
                'start_date' => $startDate,
                'auto_renewal' => true,
                'status' => $status,
            ]);
        }
    }
}
