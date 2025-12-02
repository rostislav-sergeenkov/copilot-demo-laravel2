<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Get the available expense categories.
     */
    private function getCategories(): array
    {
        return Expense::CATEGORIES;
    }

    /**
     * Apply category filter to query if provided.
     */
    private function applyCategoryFilter($query, ?string $category)
    {
        if ($category && in_array($category, Expense::CATEGORIES)) {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        
        $query = Expense::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
        
        $this->applyCategoryFilter($query, $category);
        
        $expenses = $query->paginate(15)->withQueryString();
        
        // Calculate total for filtered results
        $totalQuery = Expense::query();
        $this->applyCategoryFilter($totalQuery, $category);
        $total = $totalQuery->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => $this->getCategories(),
            'selectedCategory' => $category,
            'total' => $total,
        ]);
    }

    /**
     * Display expenses grouped by day.
     */
    public function daily(Request $request): View
    {
        $date = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : Carbon::today();
        
        $category = $request->query('category');
        
        $query = Expense::whereDate('date', $date)
            ->orderBy('created_at', 'desc');
        
        $this->applyCategoryFilter($query, $category);
        
        $expenses = $query->get();
        
        // Calculate daily total
        $dailyTotal = $expenses->sum('amount');
        
        // Group by category for breakdown
        $categoryBreakdown = $expenses->groupBy('category')->map(function ($items) {
            return [
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        });
        
        return view('expenses.daily', [
            'expenses' => $expenses,
            'date' => $date,
            'dailyTotal' => $dailyTotal,
            'categoryBreakdown' => $categoryBreakdown,
            'categories' => $this->getCategories(),
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Display expenses aggregated by month.
     */
    public function monthly(Request $request): View
    {
        $month = $request->query('month') 
            ? Carbon::parse($request->query('month') . '-01') 
            : Carbon::today()->startOfMonth();
        
        $category = $request->query('category');
        
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        
        $query = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
        
        $this->applyCategoryFilter($query, $category);
        
        $expenses = $query->get();
        
        // Calculate monthly total
        $monthlyTotal = $expenses->sum('amount');
        
        // Category breakdown with percentages
        $categoryBreakdown = $expenses->groupBy('category')->map(function ($items) use ($monthlyTotal) {
            $total = $items->sum('amount');
            return [
                'total' => $total,
                'count' => $items->count(),
                'percentage' => $monthlyTotal > 0 ? round(($total / $monthlyTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('total');
        
        // Daily breakdown
        $dailyBreakdown = $expenses->groupBy(function ($expense) {
            return $expense->date->format('Y-m-d');
        })->map(function ($items) {
            return [
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'date' => $items->first()->date,
            ];
        })->sortByDesc(function ($item) {
            return $item['date'];
        });
        
        return view('expenses.monthly', [
            'expenses' => $expenses,
            'month' => $month,
            'monthlyTotal' => $monthlyTotal,
            'categoryBreakdown' => $categoryBreakdown,
            'dailyBreakdown' => $dailyBreakdown,
            'categories' => $this->getCategories(),
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        $categories = Expense::CATEGORIES;

        return view('expenses.create', compact('categories'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        Expense::create($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): View
    {
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        $categories = Expense::CATEGORIES;

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense from storage (soft delete).
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
