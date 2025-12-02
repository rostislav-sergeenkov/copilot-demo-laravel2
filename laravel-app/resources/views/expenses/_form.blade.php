{{-- Expense Form Partial - Used by create and edit views --}}

<div class="form-group">
    <label for="description" class="form-label required">Description</label>
    <input type="text" 
           id="description" 
           name="description" 
           class="form-control @error('description') is-invalid @enderror" 
           value="{{ old('description', $expense->description ?? '') }}"
           placeholder="Enter expense description"
           maxlength="255"
           required
           aria-describedby="descriptionHelp @error('description') descriptionError @enderror">
    <div id="descriptionHelp" class="form-helper">Brief description of the expense (max 255 characters)</div>
    @error('description')
        <div id="descriptionError" class="form-error" role="alert">
            <span class="material-icons">error</span>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-group">
    <label for="amount" class="form-label required">Amount</label>
    <input type="number" 
           id="amount" 
           name="amount" 
           class="form-control @error('amount') is-invalid @enderror" 
           value="{{ old('amount', isset($expense) ? number_format($expense->amount, 2, '.', '') : '') }}"
           placeholder="0.00"
           min="0.01"
           max="999999.99"
           step="0.01"
           required
           aria-describedby="amountHelp @error('amount') amountError @enderror">
    <div id="amountHelp" class="form-helper">Amount in USD (e.g., 25.99)</div>
    @error('amount')
        <div id="amountError" class="form-error" role="alert">
            <span class="material-icons">error</span>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-group">
    <label for="category" class="form-label required">Category</label>
    <select id="category" 
            name="category" 
            class="form-control @error('category') is-invalid @enderror"
            required
            aria-describedby="@error('category') categoryError @enderror">
        <option value="">Select a category</option>
        @foreach($categories as $category)
            <option value="{{ $category }}" 
                    {{ old('category', $expense->category ?? '') === $category ? 'selected' : '' }}>
                {{ $category }}
            </option>
        @endforeach
    </select>
    @error('category')
        <div id="categoryError" class="form-error" role="alert">
            <span class="material-icons">error</span>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-group">
    <label for="date" class="form-label required">Date</label>
    <input type="date" 
           id="date" 
           name="date" 
           class="form-control @error('date') is-invalid @enderror" 
           value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : date('Y-m-d')) }}"
           max="{{ date('Y-m-d') }}"
           required
           aria-describedby="dateHelp @error('date') dateError @enderror">
    <div id="dateHelp" class="form-helper">Date of the expense (cannot be in the future)</div>
    @error('date')
        <div id="dateError" class="form-error" role="alert">
            <span class="material-icons">error</span>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
        Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <span class="material-icons">save</span>
        {{ isset($expense) ? 'Update Expense' : 'Save Expense' }}
    </button>
</div>
