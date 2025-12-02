@extends('layouts.app')

@section('title', 'Add Expense - Expense Tracker')

@section('content')
<div class="page-header">
    <h1 class="page-title">Add New Expense</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expenses.store') }}" method="POST" novalidate>
            @csrf
            @include('expenses._form')
        </form>
    </div>
</div>
@endsection
