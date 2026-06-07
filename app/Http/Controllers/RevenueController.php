<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Revenue;
use App\Models\Subscription;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index()
    {
        $revenues = Revenue::with(['client', 'subscription.service'])
            ->orderByDesc('date')
            ->paginate(15);
        return view('revenues.index', compact('revenues'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $subscriptions = Subscription::with(['client', 'service'])->where('status', 'actif')->get();
        return view('revenues.create', compact('clients', 'subscriptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:paid,pending',
        ]);
        Revenue::create($data);
        return redirect()->route('revenues.index')->with('success', 'Revenu ajouté.');
    }

    public function edit(Revenue $revenue)
    {
        $clients = Client::orderBy('name')->get();
        $subscriptions = Subscription::with(['client', 'service'])->get();
        return view('revenues.edit', compact('revenue', 'clients', 'subscriptions'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:paid,pending',
        ]);
        $revenue->update($data);
        return redirect()->route('revenues.index')->with('success', 'Revenu mis à jour.');
    }

    public function destroy(Revenue $revenue)
    {
        $revenue->delete();
        return redirect()->route('revenues.index')->with('success', 'Revenu supprimé.');
    }
}
