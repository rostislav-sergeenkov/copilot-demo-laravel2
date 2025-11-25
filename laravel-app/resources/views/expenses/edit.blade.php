@extends('layouts.app')
@section('content')
    <h1>Edit Expense</h1>
    <form action="{{ route('expenses.update', $expense) }}" method="POST">
        @csrf
        @method('PUT')
        <label>Description:</label>
        <input type="text" name="description" value="{{ $expense->description }}" required>
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="{{ $expense->amount }}" required>
        <label>Category:</label>
        <input type="text" name="category" value="{{ $expense->category }}" required>
        <label>Date:</label>
        <input type="date" name="date" value="{{ $expense->date }}" required>
        <button type="submit">Update</button>
    </form>
@endsection