<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('activeSubscriptions')->orderBy('name')->paginate(15);
        return view('services.index', compact('services'));
    }

    public function show(Service $service)
    {
        $service->load(['subscriptions.client', 'subscriptions.revenues']);
        return view('services.show', compact('service'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly,annual',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:actif,inactif',
            'description' => 'nullable|string',
        ]);
        Service::create($data);
        return redirect()->route('services.index')->with('success', 'Service créé avec succès.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly,annual',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:actif,inactif',
            'description' => 'nullable|string',
        ]);
        $service->update($data);
        return redirect()->route('services.show', $service)->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service supprimé.');
    }
}
