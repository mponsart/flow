<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'company' => 'nullable',
            'phone' => 'nullable',
            'status' => 'required',
            'notes' => 'nullable',
        ]);
        $client = Client::create($data);
        return redirect()->route('clients.show', $client);
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'sometimes',
            'email' => 'sometimes|email',
            'company' => 'nullable',
            'phone' => 'nullable',
            'status' => 'sometimes',
            'notes' => 'nullable',
        ]);
        $client->update($data);
        return redirect()->route('clients.show', $client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index');
    }
}
