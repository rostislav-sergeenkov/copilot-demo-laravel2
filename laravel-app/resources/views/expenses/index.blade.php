@extends('layouts.app')
@section('content')
    <h1>Expenses</h1>
    <form method="GET" action="{{ route('expenses.index') }}" class="mb-3">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @if($category === $cat) selected @endif>{{ $cat }}</option>
            @endforeach
        </select>
        <noscript><button type="submit">Apply</button></noscript>
    </form>
    <a href="{{ route('expenses.export.monthly', ['category' => $category]) }}" class="btn btn-sm btn-secondary mb-3">Export Monthly CSV</a>
    <h2>Daily Expenses</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyExpenses as $expense)
                <tr>
                    <td>{{ $expense->description }}</td>
                    <td>{{ $expense->amount }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->date }}</td>
                    <td>
                        <a href="{{ route('expenses.edit', $expense) }}">Edit</a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h2>Monthly Expenses</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyExpenses as $expense)
                <tr>
                    <td>{{ $expense->description }}</td>
                    <td>{{ $expense->amount }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('expenses.create') }}">Add Expense</a>
@endsection