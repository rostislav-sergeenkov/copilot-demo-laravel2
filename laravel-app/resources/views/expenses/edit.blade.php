@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Edit Expense</h2>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>
    
    <form action="{{ route('expenses.update', $expense) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="description">Description *</label>
            <input 
                type="text" 
                id="description" 
                name="description" 
                class="form-control @error('description') is-invalid @enderror" 
                value="{{ old('description', $expense->description) }}"
                placeholder="e.g., Lunch at restaurant"
                required
            >
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="amount">Amount ($) *</label>
            <input 
                type="number" 
                id="amount" 
                name="amount" 
                class="form-control @error('amount') is-invalid @enderror" 
                value="{{ old('amount', $expense->amount) }}"
                step="0.01"
                min="0.01"
                max="999999.99"
                placeholder="0.00"
                required
            >
            @error('amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select 
                id="category" 
                name="category" 
                class="form-control @error('category') is-invalid @enderror"
                required
            >
                <option value="">-- Select Category --</option>
                @foreach(\App\Models\Expense::CATEGORIES as $category)
                    <option value="{{ $category }}" {{ old('category', $expense->category) == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="date">Date *</label>
            <input 
                type="date" 
                id="date" 
                name="date" 
                class="form-control @error('date') is-invalid @enderror" 
                value="{{ old('date', $expense->date->format('Y-m-d')) }}"
                max="{{ date('Y-m-d') }}"
                required
            >
            @error('date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">💾 Update Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="margin-left: auto;" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">🗑️ Delete</button>
            </form>
        </div>
    </form>
</div>
@endsection
