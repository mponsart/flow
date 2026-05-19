<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Service::all());
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'price' => 'required|numeric',
            'monthly_cost' => 'required|numeric',
            'annual_cost' => 'required|numeric',
            'status' => 'required',
        ]);
        $service = Service::create($data);
        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes',
            'type' => 'sometimes',
            'price' => 'sometimes|numeric',
            'monthly_cost' => 'sometimes|numeric',
            'annual_cost' => 'sometimes|numeric',
            'status' => 'sometimes',
        ]);
        $service->update($data);
        return response()->json($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json(['message' => 'Service supprimé']);
    }
}
