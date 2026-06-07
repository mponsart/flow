<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['client', 'service'])
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $clients = Client::where('status', 'actif')->orderBy('name')->get();
        $services = Service::where('status', 'actif')->orderBy('name')->get();
        return view('subscriptions.create', compact('clients', 'services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'cycle' => 'required|in:monthly,annual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'auto_renewal' => 'boolean',
            'status' => 'required|in:actif,inactif,expiré',
            'notes' => 'nullable|string',
        ]);
        $data['auto_renewal'] = $request->boolean('auto_renewal');
        Subscription::create($data);
        return redirect()->route('subscriptions.index')->with('success', 'Abonnement créé.');
    }

    public function edit(Subscription $subscription)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('subscriptions.edit', compact('subscription', 'clients', 'services'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'cycle' => 'required|in:monthly,annual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'auto_renewal' => 'boolean',
            'status' => 'required|in:actif,inactif,expiré',
            'notes' => 'nullable|string',
        ]);
        $data['auto_renewal'] = $request->boolean('auto_renewal');
        $subscription->update($data);
        return redirect()->route('subscriptions.index')->with('success', 'Abonnement mis à jour.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('subscriptions.index')->with('success', 'Abonnement supprimé.');
    }
}
