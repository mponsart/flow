<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Marie Dupont', 'email' => 'marie.dupont@asso-insertion.fr', 'company' => 'Association d\'Insertion Professionnelle', 'phone' => '01 42 56 78 90', 'status' => 'actif', 'notes' => 'Client historique depuis 2022. Très fidèle.'],
            ['name' => 'Jean-Pierre Martin', 'email' => 'jp.martin@sportspopulaires.fr', 'company' => 'Club Sportif Les Populaires', 'phone' => '04 91 23 45 67', 'status' => 'actif', 'notes' => 'Association sportive. Besoin de support technique régulier.'],
            ['name' => 'Sophie Bernard', 'email' => 'sophie@culture-et-partage.org', 'company' => 'Culture & Partage', 'phone' => '03 22 11 55 44', 'status' => 'actif', 'notes' => 'Association culturelle active. Développe ses activités numériques.'],
            ['name' => 'Thomas Leroy', 'email' => 'thomas.leroy@enviro-asso.fr', 'company' => 'Environnement & Avenir', 'phone' => '02 98 76 54 32', 'status' => 'actif', 'notes' => 'Association environnementale. Abonnement annuel.'],
            ['name' => 'Claire Moreau', 'email' => 'claire.moreau@aide-handicap.fr', 'company' => 'Aide & Handicap 44', 'phone' => '02 40 12 34 56', 'status' => 'actif', 'notes' => 'Nécessite une accessibilité numérique maximale.'],
            ['name' => 'Antoine Petit', 'email' => 'antoine@solidarite-emploi.fr', 'company' => 'Solidarité Emploi Bretagne', 'phone' => '02 98 44 55 66', 'status' => 'actif', 'notes' => 'Nouvelles demandes de fonctionnalités récurrentes.'],
            ['name' => 'Lucie Fontaine', 'email' => 'lucie.fontaine@arts-vivants.fr', 'company' => 'Arts Vivants Association', 'phone' => '05 61 22 33 44', 'status' => 'inactif', 'notes' => 'Contrat suspendu en attente de renouvellement budgétaire.'],
            ['name' => 'Marc Gauthier', 'email' => 'marc.gauthier@quartier-solidaire.org', 'company' => 'Quartier Solidaire', 'phone' => '01 56 78 90 12', 'status' => 'actif', 'notes' => 'Association de quartier en forte croissance.'],
        ];

        foreach ($clients as $data) {
            Client::create($data);
        }
    }
}
