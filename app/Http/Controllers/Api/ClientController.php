<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Client::all());
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json($client);
    }

    public function store(Request $request): JsonResponse
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
        return response()->json($client, 201);
    }

    public function update(Request $request, Client $client): JsonResponse
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
        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();
        return response()->json(['message' => 'Client supprimé']);
    }
}
