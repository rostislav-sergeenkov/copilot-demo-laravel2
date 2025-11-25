@extends('layouts.app')
@section('content')
    <h1>Add Expense</h1>
    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf
        <label>Description:</label>
        <input type="text" name="description" required>
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        <label>Category:</label>
        <input type="text" name="category" required>
        <label>Date:</label>
        <input type="date" name="date" required>
        <button type="submit">Add</button>
    </form>
@endsection