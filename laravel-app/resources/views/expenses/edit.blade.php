@extends('layouts.app')
@section('content')
    <h1>Edit Expense</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were validation errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('expenses.update', $expense) }}" method="POST">
        @csrf
        @method('PUT')
        <label>Description:</label>
        <input type="text" name="description" value="{{ old('description', $expense->description) }}" required>
        @error('description')<div class="text-danger">{{ $message }}</div>@enderror
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required>
        @error('amount')<div class="text-danger">{{ $message }}</div>@enderror
        <label>Category:</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(old('category', $expense->category) === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        @error('category')<div class="text-danger">{{ $message }}</div>@enderror
        <label>Date:</label>
        <input type="date" name="date" value="{{ old('date', $expense->date) }}" required>
        @error('date')<div class="text-danger">{{ $message }}</div>@enderror
        <button type="submit">Update</button>
    </form>
@endsection