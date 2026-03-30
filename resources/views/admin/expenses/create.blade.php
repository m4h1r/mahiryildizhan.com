@extends('admin.layout', ['title' => 'New Expense', 'heading' => 'New Expense'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.expenses.store') }}">
            @csrf
            @include('admin.expenses._form')
        </form>
    </section>
@endsection
