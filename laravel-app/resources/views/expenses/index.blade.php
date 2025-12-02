@extends('layouts.app')

@section('title', 'Expenses - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Expenses</h1>
    <div class="page-header-actions">
        <a href="{{ route('expenses.daily') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons">today</span>
            Daily
        </a>
        <a href="{{ route('expenses.monthly') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons">calendar_month</span>
            Monthly
        </a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <span class="material-icons">add</span>
            Add Expense
        </a>
    </div>
</div>

<!-- Category Filter -->
<div class="filter-bar card">
    <div class="filter-group">
        <label for="categoryFilter" class="filter-label">
            <span class="material-icons">filter_list</span>
            Filter by Category:
        </label>
        <form method="GET" action="{{ route('expenses.index') }}" class="filter-form">
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
            <a href="{{ route('expenses.index') }}" 
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

<!-- Summary Card -->
@if($expenses->isNotEmpty())
<div class="summary-card card">
    <div class="summary-content">
        <div class="summary-main">
            <span class="summary-label">Total</span>
            <span class="summary-value">${{ number_format($total, 2) }}</span>
        </div>
        <div class="summary-meta">
            <span class="summary-count">{{ $expenses->total() }} expense{{ $expenses->total() !== 1 ? 's' : '' }}</span>
            @if($selectedCategory)
                <span class="summary-filter">in {{ $selectedCategory }}</span>
            @endif
        </div>
    </div>
</div>
@endif

<div class="card">
    @if($expenses->isEmpty())
        <div class="empty-state">
            <span class="material-icons empty-state-icon">receipt_long</span>
            @if($selectedCategory)
                <h2 class="empty-state-title">No {{ $selectedCategory }} expenses</h2>
                <p class="empty-state-message">No expenses found in this category.</p>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                    <span class="material-icons">clear_all</span>
                    View All Expenses
                </a>
            @else
                <h2 class="empty-state-title">No expenses yet</h2>
                <p class="empty-state-message">Start tracking your spending by adding your first expense.</p>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                    <span class="material-icons">add</span>
                    Add Your First Expense
                </a>
            @endif
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td class="date">{{ $expense->date->format('M d, Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>
                                <span class="category-badge {{ Str::slug($expense->category) }}">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="amount">${{ number_format($expense->amount, 2) }}</td>
                            <td class="actions">
                                <a href="{{ route('expenses.edit', $expense) }}" 
                                   class="btn btn-icon" 
                                   title="Edit expense"
                                   aria-label="Edit {{ $expense->description }}">
                                    <span class="material-icons">edit</span>
                                </a>
                                <button type="button" 
                                        class="btn btn-icon btn-danger" 
                                        title="Delete expense"
                                        aria-label="Delete {{ $expense->description }}"
                                        onclick="confirmDelete(event, {{ $expense->id }}, '{{ addslashes($expense->description) }}')">
                                    <span class="material-icons">delete</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="card-footer">
                <nav class="pagination" aria-label="Expense pagination">
                    {{-- Previous Page Link --}}
                    @if($expenses->onFirstPage())
                        <span class="pagination-link disabled" aria-disabled="true">
                            <span class="material-icons">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $expenses->previousPageUrl() }}" class="pagination-link" rel="prev" aria-label="Previous page">
                            <span class="material-icons">chevron_left</span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach($expenses->getUrlRange(1, $expenses->lastPage()) as $page => $url)
                        @if($page == $expenses->currentPage())
                            <span class="pagination-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($expenses->hasMorePages())
                        <a href="{{ $expenses->nextPageUrl() }}" class="pagination-link" rel="next" aria-label="Next page">
                            <span class="material-icons">chevron_right</span>
                        </a>
                    @else
                        <span class="pagination-link disabled" aria-disabled="true">
                            <span class="material-icons">chevron_right</span>
                        </span>
                    @endif
                </nav>
            </div>
        @endif
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" role="dialog" aria-labelledby="deleteModalTitle" aria-modal="true">
        <div class="modal-header">
            <h2 class="modal-title" id="deleteModalTitle">Delete Expense</h2>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this expense?</p>
            <p class="text-muted"><strong id="deleteExpenseDescription"></strong></p>
            <p class="text-muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">
                Cancel
            </button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <span class="material-icons">delete</span>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
