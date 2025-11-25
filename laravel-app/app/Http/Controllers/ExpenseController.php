<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $category = $request->query('category');

        $dailyQuery = Expense::where('date', $today);
        $monthlyQuery = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year);

        if ($category) {
            $dailyQuery->where('category', $category);
            $monthlyQuery->where('category', $category);
        }

        $dailyExpenses = $dailyQuery->get();
        $monthlyExpenses = $monthlyQuery->get();
        $categories = Expense::select('category')->distinct()->pluck('category');

        return view('expenses.index', compact('dailyExpenses', 'monthlyExpenses', 'categories', 'category'));
    }

    /**
     * Export monthly expenses to CSV.
     */
    public function exportMonthlyCsv(Request $request)
    {
        $category = $request->query('category');
        $monthlyQuery = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year);
        if ($category) {
            $monthlyQuery->where('category', $category);
        }
        $monthlyExpenses = $monthlyQuery->get();

        $filename = 'monthly_expenses_' . now()->format('Y_m') . ($category ? '_' . str_replace(' ', '_', $category) : '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['Description', 'Amount', 'Category', 'Date'];
        $callback = function () use ($monthlyExpenses, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($monthlyExpenses as $expense) {
                fputcsv($handle, [
                    $expense->description,
                    $expense->amount,
                    $expense->category,
                    $expense->date,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
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
