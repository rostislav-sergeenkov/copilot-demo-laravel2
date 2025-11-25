<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = now()->toDateString();
        $dailyExpenses = Expense::where('date', $today)->get();
        $monthlyExpenses = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->get();
        return view('expenses.index', compact('dailyExpenses', 'monthlyExpenses'));

        /**
         * Show the form for creating a new expense.
         */
        public function create()
        {
            return view('expenses.create');
        }

        /**
         * Store a newly created expense in storage.
         */
        public function store(Request $request)
        {
            $validated = $request->validate([
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'category' => 'required|string|max:255',
                'date' => 'required|date',
            ]);
            Expense::create($validated);
            return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
        }

        /**
         * Show the form for editing the specified expense.
         */
        public function edit(Expense $expense)
        {
            return view('expenses.edit', compact('expense'));
        }

        /**
         * Update the specified expense in storage.
         */
        public function update(Request $request, Expense $expense)
        {
            $validated = $request->validate([
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'category' => 'required|string|max:255',
                'date' => 'required|date',
            ]);
            $expense->update($validated);
            return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
        }

        /**
         * Remove the specified expense from storage.
         */
        public function destroy(Expense $expense)
        {
            $expense->delete();
            return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
        }
    }

}
