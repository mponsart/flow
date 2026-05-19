<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('services.index', compact('services'));
    }

    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
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
        return redirect()->route('services.show', $service);
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
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
        return redirect()->route('services.show', $service);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index');
    }
}
