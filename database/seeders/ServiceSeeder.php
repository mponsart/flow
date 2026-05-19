<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'VPS Starter',
                'type' => 'mensuel',
                'price' => 29.00,
                'monthly_cost' => 10.00,
                'annual_cost' => 100.00,
                'status' => 'actif',
            ],
            [
                'name' => 'VPS Pro',
                'type' => 'annuel',
                'price' => 299.00,
                'monthly_cost' => 25.00,
                'annual_cost' => 250.00,
                'status' => 'actif',
            ],
            [
                'name' => 'Email Business',
                'type' => 'mensuel',
                'price' => 9.00,
                'monthly_cost' => 2.00,
                'annual_cost' => 20.00,
                'status' => 'actif',
            ],
            [
                'name' => 'Cloud Backup',
                'type' => 'annuel',
                'price' => 99.00,
                'monthly_cost' => 8.00,
                'annual_cost' => 80.00,
                'status' => 'inactif',
            ],
        ];
        foreach ($services as $service) {
            \App\Models\Service::create($service);
        }
    }
}
