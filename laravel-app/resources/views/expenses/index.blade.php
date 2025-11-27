@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>All Expenses</h2>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ Add New Expense</a>
    </div>
    
    @if($expenses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->date->format('M d, Y') }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower(str_replace(' ', '-', $expense->category)) }}">
                            {{ $expense->category }}
                        </span>
                    </td>
                    <td class="amount">${{ number_format($expense->amount, 2) }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary btn-small">Edit</a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align: right;">Total:</th>
                    <th class="amount">${{ number_format($expenses->sum('amount'), 2) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        
        <div class="pagination">
            {{ $expenses->links() }}
        </div>
    @else
        <div class="empty-state">
            <p style="font-size: 1.2rem; margin-bottom: 1rem;">No expenses found</p>
            <p>Start tracking your expenses by adding your first entry!</p>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary" style="margin-top: 1rem;">+ Add Your First Expense</a>
        </div>
    @endif
</div>
@endsection
