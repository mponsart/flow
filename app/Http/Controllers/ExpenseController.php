<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Service;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public const CATEGORIES = ['Hébergement', 'Infrastructure', 'Personnel', 'Marketing', 'Autre'];

    public function index()
    {
        $expenses = Expense::with(['client', 'service'])
            ->orderByDesc('date')
            ->paginate(15);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $categories = self::CATEGORIES;
        return view('expenses.create', compact('clients', 'services', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'Dépense ajoutée.');
    }

    public function edit(Expense $expense)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $categories = self::CATEGORIES;
        return view('expenses.edit', compact('expense', 'clients', 'services', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);
        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Dépense supprimée.');
    }
}