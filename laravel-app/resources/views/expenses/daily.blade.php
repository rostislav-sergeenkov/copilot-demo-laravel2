@extends('layouts.app')

@section('title', 'Daily Expenses - ' . $date->format('M d, Y') . ' - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Daily Expenses</h1>
    <div class="page-header-actions">
        <a href="{{ route('expenses.daily', ['date' => now()->format('Y-m-d'), 'category' => $selectedCategory]) }}" 
           class="btn btn-secondary btn-sm">
            Today
        </a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <span class="material-icons">add</span>
            Add Expense
        </a>
    </div>
</div>

<!-- Date Navigation -->
<div class="date-navigation card">
    <a href="{{ route('expenses.daily', ['date' => $date->copy()->subDay()->format('Y-m-d'), 'category' => $selectedCategory]) }}" 
       class="date-nav-btn" 
       aria-label="Previous day">
        <span class="material-icons">chevron_left</span>
    </a>
    <div class="date-display">
        <span class="date-weekday">{{ $date->format('l') }}</span>
        <span class="date-full">{{ $date->format('F j, Y') }}</span>
    </div>
    <a href="{{ route('expenses.daily', ['date' => $date->copy()->addDay()->format('Y-m-d'), 'category' => $selectedCategory]) }}" 
       class="date-nav-btn"
       aria-label="Next day"
       @if($date->isToday()) style="visibility: hidden;" @endif>
        <span class="material-icons">chevron_right</span>
    </a>
    <form class="date-picker-form" method="GET" action="{{ route('expenses.daily') }}">
        @if($selectedCategory)
            <input type="hidden" name="category" value="{{ $selectedCategory }}">
        @endif
        <input type="date" 
               name="date" 
               value="{{ $date->format('Y-m-d') }}" 
               class="form-control date-picker-input"
               max="{{ now()->format('Y-m-d') }}"
               onchange="this.form.submit()"
               aria-label="Select date">
    </form>
</div>

<!-- Category Filter -->
<div class="filter-bar card">
    <div class="filter-group">
        <label for="categoryFilter" class="filter-label">
            <span class="material-icons">filter_list</span>
            Filter by Category:
        </label>
        <form method="GET" action="{{ route('expenses.daily') }}" class="filter-form">
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            <select name="category" id="categoryFilter" class="form-control filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </form>
        @if($selectedCategory)
            <a href="{{ route('expenses.daily', ['date' => $date->format('Y-m-d')]) }}" 
               class="btn btn-text btn-sm filter-clear"
               title="Clear filter">
                <span class="material-icons">close</span>
                Clear
            </a>
        @endif
    </div>
    @if($selectedCategory)
        <div class="active-filter-indicator">
            <span class="material-icons">filter_alt</span>
            Showing: <strong>{{ $selectedCategory }}</strong>
        </div>
    @endif
</div>

<!-- Daily Summary -->
<div class="summary-card card">
    <div class="summary-content">
        <div class="summary-main">
            <span class="summary-label">Daily Total</span>
            <span class="summary-value">${{ number_format($dailyTotal, 2) }}</span>
        </div>
        <div class="summary-meta">
            <span class="summary-count">{{ $expenses->count() }} expense{{ $expenses->count() !== 1 ? 's' : '' }}</span>
            @if($selectedCategory)
                <span class="summary-filter">in {{ $selectedCategory }}</span>
            @endif
        </div>
    </div>
</div>

<!-- Expenses List -->
<div class="card">
    @if($expenses->isEmpty())
        <div class="empty-state">
            <span class="material-icons empty-state-icon">receipt_long</span>
            <h2 class="empty-state-title">No expenses for this day</h2>
            <p class="empty-state-message">
                @if($selectedCategory)
                    No {{ $selectedCategory }} expenses recorded on {{ $date->format('F j, Y') }}.
                @else
                    No expenses recorded on {{ $date->format('F j, Y') }}.
                @endif
            </p>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <span class="material-icons">add</span>
                Add Expense
            </a>
        </div>
    @else
        <div class="expense-list">
            @foreach($expenses as $expense)
                <div class="expense-item">
                    <div class="expense-info">
                        <span class="expense-description">{{ $expense->description }}</span>
                        <span class="category-badge {{ Str::slug($expense->category) }}">
                            {{ $expense->category }}
                        </span>
                    </div>
                    <div class="expense-amount">${{ number_format($expense->amount, 2) }}</div>
                    <div class="expense-actions">
                        <a href="{{ route('expenses.edit', $expense) }}" 
                           class="btn btn-icon" 
                           title="Edit expense"
                           aria-label="Edit {{ $expense->description }}">
                            <span class="material-icons">edit</span>
                        </a>
                        <a href="{{ route('expenses.show', $expense) }}" 
                           class="btn btn-icon" 
                           title="View details"
                           aria-label="View {{ $expense->description }}">
                            <span class="material-icons">visibility</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Category Breakdown (if multiple categories) -->
@if($categoryBreakdown->count() > 1)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Category Breakdown</h3>
    </div>
    <div class="card-body">
        <div class="breakdown-list">
            @foreach($categoryBreakdown as $category => $data)
                <div class="breakdown-item">
                    <div class="breakdown-category">
                        <span class="category-badge {{ Str::slug($category) }}">{{ $category }}</span>
                        <span class="breakdown-count">{{ $data['count'] }} item{{ $data['count'] !== 1 ? 's' : '' }}</span>
                    </div>
                    <div class="breakdown-amount">${{ number_format($data['total'], 2) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Navigation Links -->
<div class="view-navigation">
    <a href="{{ route('expenses.index', ['category' => $selectedCategory]) }}" class="btn btn-secondary">
        <span class="material-icons">list</span>
        All Expenses
    </a>
    <a href="{{ route('expenses.monthly', ['month' => $date->format('Y-m'), 'category' => $selectedCategory]) }}" class="btn btn-secondary">
        <span class="material-icons">calendar_month</span>
        Monthly View
    </a>
</div>
@endsection
