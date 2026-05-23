<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Service;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index()
    {
        $expenses = Expense::with('service')->orderByDesc('date')->paginate(15);
        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(Request $request)
    {
        $service_id = $request->get('service_id');
        return view('expenses.create', compact('service_id'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'category' => 'required',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'note' => 'nullable',
        ]);
        \App\Models\Expense::create($data);
        return redirect()->route('services.show', $data['service_id']);
    }

    /**
     * Display the specified expense.
     */
    public function show(string $id)
    {
        $expense = Expense::findOrFail($id);
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(string $id)
    {
        $expense = Expense::findOrFail($id);
        return view('expenses.edit', compact('expense'));
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update($request->validate([
            'category' => 'required',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'note' => 'nullable',
        ]));
        return redirect()->route('expenses.show', $expense->id);
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('expenses.index');
    }
}