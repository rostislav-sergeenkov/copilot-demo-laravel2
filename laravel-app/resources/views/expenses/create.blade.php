@extends('layouts.app')

@section('title', 'Add New Expense')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Add New Expense</h2>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>
    
    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="description">Description *</label>
            <input 
                type="text" 
                id="description" 
                name="description" 
                class="form-control @error('description') is-invalid @enderror" 
                value="{{ old('description') }}"
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
                value="{{ old('amount') }}"
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
                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
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
                value="{{ old('date', date('Y-m-d')) }}"
                max="{{ date('Y-m-d') }}"
                required
            >
            @error('date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">💾 Save Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
