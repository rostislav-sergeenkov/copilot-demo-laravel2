@extends('layouts.app')

@section('title', 'Edit Expense - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Expense</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" novalidate>
            @csrf
            @method('PUT')
            @include('expenses._form')
        </form>
    </div>
</div>
@endsection
