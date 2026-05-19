<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Subscription::all());
    }

    public function show(Subscription $subscription): JsonResponse
    {
        return response()->json($subscription);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'cycle' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'required',
            'renewal' => 'required|boolean',
        ]);
        $subscription = Subscription::create($data);
        return response()->json($subscription, 201);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'sometimes|exists:clients,id',
            'service_id' => 'sometimes|exists:services,id',
            'cycle' => 'sometimes',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'status' => 'sometimes',
            'renewal' => 'sometimes|boolean',
        ]);
        $subscription->update($data);
        return response()->json($subscription);
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $subscription->delete();
        return response()->json(['message' => 'Abonnement supprimé']);
    }
}
