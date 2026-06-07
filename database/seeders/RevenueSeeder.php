<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Revenue;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = Subscription::with(['client', 'service'])->get();

        // Generate 12 months of revenues for each active subscription
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month)->startOfMonth()->addDays(rand(1, 20));

            foreach ($subscriptions as $sub) {
                if ($sub->start_date->gt($date)) continue;

                $amount = (float) $sub->service->price;
                // For annual subscriptions, only once per year
                if ($sub->cycle === 'annual') {
                    if ($date->month !== $sub->start_date->month) continue;
                    // If outside year range, skip
                }

                Revenue::create([
                    'client_id' => $sub->client_id,
                    'subscription_id' => $sub->id,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'description' => 'Facture ' . ($sub->cycle === 'monthly' ? 'mensuelle' : 'annuelle') . ' — ' . $sub->service->name,
                    'status' => 'paid',
                ]);
            }
        }

        // Add a few pending revenues
        $clients = Client::where('status', 'actif')->take(3)->get();
        foreach ($clients as $client) {
            Revenue::create([
                'client_id' => $client->id,
                'amount' => rand(30, 100),
                'date' => Carbon::now()->format('Y-m-d'),
                'description' => 'Facture en attente de règlement',
                'status' => 'pending',
            ]);
        }
    }
}
