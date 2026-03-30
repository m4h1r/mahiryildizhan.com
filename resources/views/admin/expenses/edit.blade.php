@extends('admin.layout', ['title' => 'Edit Expense', 'heading' => 'Edit Expense'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
            @csrf
            @method('PUT')
            @include('admin.expenses._form')
        </form>
    </section>
@endsection
