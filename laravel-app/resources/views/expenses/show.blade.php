@extends('layouts.app')

@section('title', 'Expense Details - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Expense Details</h1>
    <div>
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-secondary">
            <span class="material-icons">edit</span>
            Edit
        </a>
        <a href="{{ route('expenses.index') }}" class="btn btn-text">
            <span class="material-icons">arrow_back</span>
            Back to List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <dl class="detail-list">
            <div class="detail-item">
                <dt class="detail-label">Description</dt>
                <dd class="detail-value">{{ $expense->description }}</dd>
            </div>
            
            <div class="detail-item">
                <dt class="detail-label">Amount</dt>
                <dd class="detail-value amount">${{ number_format($expense->amount, 2) }}</dd>
            </div>
            
            <div class="detail-item">
                <dt class="detail-label">Category</dt>
                <dd class="detail-value">
                    <span class="category-badge {{ Str::slug($expense->category) }}">
                        {{ $expense->category }}
                    </span>
                </dd>
            </div>
            
            <div class="detail-item">
                <dt class="detail-label">Date</dt>
                <dd class="detail-value">{{ $expense->date->format('F d, Y') }}</dd>
            </div>
            
            <div class="detail-item">
                <dt class="detail-label">Created</dt>
                <dd class="detail-value text-muted">{{ $expense->created_at->format('M d, Y \a\t g:i A') }}</dd>
            </div>
            
            @if($expense->updated_at->ne($expense->created_at))
            <div class="detail-item">
                <dt class="detail-label">Last Updated</dt>
                <dd class="detail-value text-muted">{{ $expense->updated_at->format('M d, Y \a\t g:i A') }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

<style>
    .detail-list {
        margin: 0;
    }
    
    .detail-item {
        display: flex;
        padding: var(--spacing-md) 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .detail-item:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        flex: 0 0 140px;
        font-weight: 500;
        color: var(--text-secondary);
    }
    
    .detail-value {
        flex: 1;
        margin: 0;
    }
    
    .detail-value.amount {
        font-size: 1.25rem;
        font-weight: 500;
        color: var(--primary-color);
    }
    
    @media (max-width: 480px) {
        .detail-item {
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .detail-label {
            flex: none;
        }
    }
</style>
@endsection
