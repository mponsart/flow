<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Hébergement Cloud Starter', 'type' => 'monthly', 'price' => 29.90, 'cost' => 8.00, 'status' => 'actif', 'description' => 'Hébergement web mutualisé avec 10 Go de stockage, SSL inclus.'],
            ['name' => 'Hébergement Cloud Pro', 'type' => 'monthly', 'price' => 79.90, 'cost' => 20.00, 'status' => 'actif', 'description' => 'VPS dédié 4 cœurs, 8 Go RAM, 100 Go SSD. Idéal pour les associations actives.'],
            ['name' => 'CRM Associatif', 'type' => 'monthly', 'price' => 49.90, 'cost' => 12.00, 'status' => 'actif', 'description' => 'Gestion des membres, dons, événements. Interface simplifiée pour les associations.'],
            ['name' => 'Support Premium', 'type' => 'monthly', 'price' => 39.90, 'cost' => 15.00, 'status' => 'actif', 'description' => 'Assistance téléphonique prioritaire 5j/7, réponse en moins de 4h.'],
            ['name' => 'Formation Numérique', 'type' => 'annual', 'price' => 890.00, 'cost' => 250.00, 'status' => 'actif', 'description' => 'Pack de 10 sessions de formation (2h) pour former les équipes aux outils numériques.'],
            ['name' => 'Sauvegarde Cloud', 'type' => 'monthly', 'price' => 15.90, 'cost' => 4.00, 'status' => 'actif', 'description' => 'Sauvegarde automatique quotidienne des données. Conservation 30 jours.'],
        ];

        foreach ($services as $data) {
            Service::create($data);
        }
    }
}
