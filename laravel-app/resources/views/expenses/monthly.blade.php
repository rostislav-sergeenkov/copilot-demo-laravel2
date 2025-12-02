@extends('layouts.app')

@section('title', 'Monthly Expenses - ' . $month->format('F Y') . ' - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Monthly Expenses</h1>
    <div class="page-header-actions">
        <a href="{{ route('expenses.monthly', ['month' => now()->format('Y-m'), 'category' => $selectedCategory]) }}" 
           class="btn btn-secondary btn-sm">
            This Month
        </a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <span class="material-icons">add</span>
            Add Expense
        </a>
    </div>
</div>

<!-- Month Navigation -->
<div class="date-navigation card">
    <a href="{{ route('expenses.monthly', ['month' => $month->copy()->subMonth()->format('Y-m'), 'category' => $selectedCategory]) }}" 
       class="date-nav-btn" 
       aria-label="Previous month">
        <span class="material-icons">chevron_left</span>
    </a>
    <div class="date-display">
        <span class="date-full month-display">{{ $month->format('F Y') }}</span>
    </div>
    <a href="{{ route('expenses.monthly', ['month' => $month->copy()->addMonth()->format('Y-m'), 'category' => $selectedCategory]) }}" 
       class="date-nav-btn"
       aria-label="Next month"
       @if($month->isSameMonth(now())) style="visibility: hidden;" @endif>
        <span class="material-icons">chevron_right</span>
    </a>
    <form class="date-picker-form" method="GET" action="{{ route('expenses.monthly') }}">
        @if($selectedCategory)
            <input type="hidden" name="category" value="{{ $selectedCategory }}">
        @endif
        <input type="month" 
               name="month" 
               value="{{ $month->format('Y-m') }}" 
               class="form-control date-picker-input"
               max="{{ now()->format('Y-m') }}"
               onchange="this.form.submit()"
               aria-label="Select month">
    </form>
</div>

<!-- Category Filter -->
<div class="filter-bar card">
    <div class="filter-group">
        <label for="categoryFilter" class="filter-label">
            <span class="material-icons">filter_list</span>
            Filter by Category:
        </label>
        <form method="GET" action="{{ route('expenses.monthly') }}" class="filter-form">
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
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
            <a href="{{ route('expenses.monthly', ['month' => $month->format('Y-m')]) }}" 
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

<!-- Monthly Summary -->
<div class="summary-card card summary-card-large">
    <div class="summary-content">
        <div class="summary-main">
            <span class="summary-label">Monthly Total</span>
            <span class="summary-value summary-value-large">${{ number_format($monthlyTotal, 2) }}</span>
        </div>
        <div class="summary-meta">
            <span class="summary-count">{{ $expenses->count() }} expense{{ $expenses->count() !== 1 ? 's' : '' }}</span>
            @if($selectedCategory)
                <span class="summary-filter">in {{ $selectedCategory }}</span>
            @else
                <span class="summary-filter">across {{ $categoryBreakdown->count() }} categories</span>
            @endif
        </div>
    </div>
</div>

@if($expenses->isNotEmpty())
    <!-- Category Breakdown -->
    @if(!$selectedCategory && $categoryBreakdown->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <span class="material-icons">pie_chart</span>
                Category Breakdown
            </h3>
        </div>
        <div class="card-body">
            <div class="category-breakdown-grid">
                @foreach($categoryBreakdown as $category => $data)
                    <div class="category-breakdown-item">
                        <div class="category-breakdown-header">
                            <span class="category-badge {{ Str::slug($category) }}">{{ $category }}</span>
                            <span class="category-percentage">{{ $data['percentage'] }}%</span>
                        </div>
                        <div class="category-breakdown-bar">
                            <div class="category-breakdown-progress {{ Str::slug($category) }}" 
                                 style="width: {{ $data['percentage'] }}%"></div>
                        </div>
                        <div class="category-breakdown-details">
                            <span class="category-amount">${{ number_format($data['total'], 2) }}</span>
                            <span class="category-count">{{ $data['count'] }} expense{{ $data['count'] !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Daily Breakdown -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <span class="material-icons">calendar_today</span>
                Daily Breakdown
            </h3>
        </div>
        <div class="card-body">
            @if($dailyBreakdown->isEmpty())
                <p class="text-muted text-center">No expenses recorded this month.</p>
            @else
                <div class="daily-breakdown-list">
                    @foreach($dailyBreakdown as $day => $data)
                        <a href="{{ route('expenses.daily', ['date' => $day, 'category' => $selectedCategory]) }}" 
                           class="daily-breakdown-item">
                            <div class="daily-breakdown-date">
                                <span class="daily-date-day">{{ $data['date']->format('d') }}</span>
                                <span class="daily-date-weekday">{{ $data['date']->format('D') }}</span>
                            </div>
                            <div class="daily-breakdown-info">
                                <span class="daily-expense-count">{{ $data['count'] }} expense{{ $data['count'] !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="daily-breakdown-amount">
                                ${{ number_format($data['total'], 2) }}
                            </div>
                            <span class="material-icons daily-breakdown-arrow">chevron_right</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <span class="material-icons empty-state-icon">receipt_long</span>
            <h2 class="empty-state-title">No expenses this month</h2>
            <p class="empty-state-message">
                @if($selectedCategory)
                    No {{ $selectedCategory }} expenses recorded in {{ $month->format('F Y') }}.
                @else
                    No expenses recorded in {{ $month->format('F Y') }}.
                @endif
            </p>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <span class="material-icons">add</span>
                Add Expense
            </a>
        </div>
    </div>
@endif

<!-- Navigation Links -->
<div class="view-navigation">
    <a href="{{ route('expenses.index', ['category' => $selectedCategory]) }}" class="btn btn-secondary">
        <span class="material-icons">list</span>
        All Expenses
    </a>
    <a href="{{ route('expenses.daily', ['date' => now()->format('Y-m-d'), 'category' => $selectedCategory]) }}" class="btn btn-secondary">
        <span class="material-icons">today</span>
        Daily View
    </a>
</div>
@endsection
