@extends('layouts.app')

@section('title', 'Expense Details')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Expense Details</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">← Back to List</a>
        </div>
    </div>
    
    <div style="line-height: 2;">
        <p><strong>Description:</strong> {{ $expense->description }}</p>
        <p><strong>Amount:</strong> <span class="amount">${{ number_format($expense->amount, 2) }}</span></p>
        <p>
            <strong>Category:</strong> 
            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $expense->category)) }}">
                {{ $expense->category }}
            </span>
        </p>
        <p><strong>Date:</strong> {{ $expense->date->format('F d, Y') }}</p>
        <p><strong>Created:</strong> {{ $expense->created_at->format('F d, Y \a\t g:i A') }}</p>
        @if($expense->updated_at != $expense->created_at)
            <p><strong>Last Updated:</strong> {{ $expense->updated_at->format('F d, Y \a\t g:i A') }}</p>
        @endif
    </div>
</div>
@endsection
